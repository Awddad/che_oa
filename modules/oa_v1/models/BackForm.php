<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/9
 * Time: 11:01
 */

namespace app\modules\oa_v1\models;

use app\logic\MyTcPdf;
use app\models\Apply;
use app\models\JieKuan;
use app\models\PayBack;
use app\modules\oa_v1\logic\PersonLogic;
use yii\db\Exception;


/**
 * 还款表单
 *
 * Class BackForm
 * @package app\modules\oa_v1\models
 *
 * @property string $money
 * @property string $bank_card_id
 * @property string $bank_name
 * @property string $bank_name_des
 * @property string $des
 * @property array $apply_ids
 * @property integer $type
 * @property array $approval_persons
 * @property array $copy_person
 */
class BackForm extends BaseForm
{
    /**
     * 借款转入到的银行卡号
     * @var
     */
    public $bank_card_id;

    /**
     * 银行卡对应的银行
     * @var
     */
    public $bank_name;

    /**
     * 支行名称
     * @var
     */
    public $bank_name_des;

    /**
     * 借款事由
     * @var
     */
    public $des;

    /**
     * 借款IDs
     * @var
     */
    public $apply_ids = [];


    /**
     * 审批人
     * @var array
     */
    public $approval_persons = [];

    /**
     * 抄送人
     * @var array
     */
    public $copy_person = [];

    /**
     * 申请类型 还款默认为3
     * @var
     */
    public $type = 3;

    /**
     * 表单验证
     *
     * @return array
     */
    public function rules()
    {
        return [
            [
                ['bank_card_id', 'bank_name', 'apply_ids', 'approval_persons' , 'copy_person'],
                'required'
            ],
            [
                ['bank_card_id', 'bank_name', 'bank_name_des', 'des'],
                'string'
            ],
            [
                ['approval_persons', 'copy_person'],
                'each',
                'rule' => ['integer']
            ],
            [
                ['apply_ids'],
                'checkApplyIds',
            ],
        ];
    }

    /**
     * 检查还款
     *
     * @param $attribute
     */
    public function checkApplyIds($attribute)
    {
        if(empty($this->$attribute)) {
            $this->addError($attribute, '还款不能为空');
        }
        foreach ($this->$attribute as $v){
            $apply = Apply::findOne($v);
            if ($apply->status == 99) {
                $jieKuan = JieKuan::findOne($v);
                if ($jieKuan->status > 99) {
                    $this->addError($attribute, $v. '不能还款');
                }
            } else {
                $this->addError($attribute, $v. '不能还款');
            }
        }
    }

    /**
     * 保存报销申请
     *
     * @param $user
     * @return integer
     * @throws Exception
     */
    public function save($user)
    {
        $applyId = $this->createApplyId();
        $pdf = new  MyTcPdf();
        $basePath = \Yii::$app->basePath.'/web';
        $filePath = '/upload/pdf/payback/'.date('Y-m-d').'/';
        $rootPath = $basePath.$filePath;
        if (!file_exists($rootPath)) {
            @mkdir($rootPath, 0777, true);
        }
        $rst = $pdf->createHuanKuanDanPdf($rootPath.$applyId.'.pdf', [
            'list' => $this->getBackList(),
            'apply_date' => date('Y年m月d日'),
            'apply_id' => $applyId,
            'org_full_name' => PersonLogic::instance()->getOrgNameByPersonId($user['person_id']),
            'person' => $user['person_name'],
            'bank_name' => $this->bank_name,
            'bank_card_id' => $this->bank_card_id,
            'tips' => $this->tips,
            'approval_person' => $this->getPerson('approval_persons'),//多个人、分隔
            'copy_person' => $this->getPerson('copy_person'),//多个人、分隔
        ]);
        if ($rst) {
            $pdfUrl = $filePath.$applyId.'.pdf';
        } else {
            $pdfUrl = '';
        }
        $nextName = PersonLogic::instance()->getPersonName($this->approval_persons[0]);
        $apply = new Apply();
        $apply->apply_id = $applyId;
        $apply->title = $this->createApplyTitle($user);
        $apply->create_time = $_SERVER['REQUEST_TIME'];
        $apply->type = $this->type;
        $apply->person_id = $user['person_id'];
        $apply->person = $user['person_name'];
        $apply->status = 1;
        $apply->next_des = '等待'.$nextName.'审批';
        $apply->approval_persons = $this->getPerson('approval_persons');
        $apply->copy_person = $this->getPerson('copy_person');
        $apply->org_id = $user['org_id'];
        $apply->apply_list_pdf = $pdfUrl;
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();
        try{
            if (!$apply->save()) {
                throw new Exception('申请失败',$apply->errors);
            }
            $this->approvalPerson($apply);
            $this->copyPerson($apply);
            $this->saveBack($apply);
            //改变借款单状态
            foreach ($this->apply_ids as $apply_id) {
                JieKuan::updateAll(['status' => 99], ['apply_id' => $apply_id]);
            }
            $transaction->commit();

        } catch (Exception $exception){
            $transaction->rollBack();
            throw $exception;
        }
        return $apply->apply_id;
    }

    /**
     * 保存还款信息
     *
     * @param $apply
     * @return PayBack
     * @throws Exception
     */
    public function saveBack($apply)
    {
        $model = new PayBack();
        $model->apply_id = $apply->apply_id;
        $model->jie_kuan_ids = implode(',', $this->apply_ids);
        $model->money = $this->getMoney();
        $model->bank_card_id = $this->bank_card_id;
        $model->bank_name = $this->bank_name;
        $model->bank_name_des = $this->bank_name_des ? : '';
        if (!$model->save()) {
            throw new Exception('借款保存失败', $model->errors);
        }
        return $model;
    }

    /**
     * 获取金额
     *
     * @return string
     */
    public function getMoney()
    {
        $money = 0;
        foreach ($this->apply_ids as $apply_id) {
            $loan = JieKuan::findOne($apply_id);
            $money += floatval($loan->money);
        }
        return $money;
    }

    /**
     * 报销list
     *
     * @return array
     */
    public function getBackList()
    {
        $data = [];
        foreach ($this->apply_ids as $apply_id) {
            $back = JieKuan::findOne($apply_id);
            $data[] = [
                'create_time' => date('Y-m-d H:i', $back->apply->create_time),
                'money' => $back->money,
                'detail' => $back->des
            ];
        }

        return $data;
    }
}