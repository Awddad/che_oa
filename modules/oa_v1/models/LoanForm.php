<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/5
 * Time: 11:10
 */

namespace app\modules\oa_v1\models;


use app\logic\MyTcPdf;
use app\modules\oa_v1\logic\PersonLogic;
use app\models\Apply;
use app\models\JieKuan;
use yii\db\Exception;


/**
 * 借款表单
 *
 * Class LoanForm
 * @package app\modules\oa_v1\models
 *
 * @property string $bank_card_id
 * @property string $bank_name
 * @property string $bank_name_des
 * @property string $tips
 * @property string $des
 * @property string $pics
 * @property string $money
 * @property array $approval_persons
 * @property array $copy_person
 * @property int $type
 * @property string $apply_id
 */
class LoanForm extends BaseForm
{
    /**
     * 借款金额
     * @var
     */
    public $money;

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
     * 备注
     * @var
     */
    public $tips;

    /**
     * 借款事由
     * @var
     */
    public $des;

    /**
     * 上传图片
     * @var
     */
    public $pics;

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
     * 申请类型 借款默认为2
     * @var
     */
    public $type = 2;

    /**
     * 申请ID
     * @var
     */
    public $apply_id;

    /**
     * 表单验证
     * @return array
     */
    public function rules()
    {
        return [
            [
                ['money', 'bank_card_id', 'bank_name', 'des', 'approval_persons', 'apply_id'],
                'required',
                'message' => '缺少必填字段'
            ],
            [
                ['approval_persons', 'copy_person'],
                'each',
                'rule' => ['integer']
            ],
            [
                ['pics'], 'string'
            ],
            [
                ['money', 'bank_card_id', 'bank_name', 'bank_name_des','des', 'tips', 'apply_id'],
                'string'
            ]
        ];
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
        $applyId = $this->apply_id;
        $pdf = new  MyTcPdf();
        $basePath = \Yii::$app->basePath.'/web';
        $filePath = '/upload/pdf/loan/'.date('Y-m-d').'/';
        $rootPath = $basePath.$filePath;
        if (!file_exists($rootPath)) {
            @mkdir($rootPath, 0777, true);
        }
        $rst = $pdf->createJieKuanDanPdf($rootPath.$applyId.'.pdf', [
            'apply_date' => date('Y年m月d日'),
            'apply_id' => $applyId,
            'org_full_name' => PersonLogic::instance()->getOrgNameByPersonId($user['person_id']),
            'person' => $user['person_name'],
            'bank_name' => $this->bank_name,
            'bank_card_id' => $this->bank_card_id,
            'money' => $this->money,
            'detail' => $this->des,
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
        $apply->apply_list_pdf = $pdfUrl;
        $apply->org_id = $user['org_id'];
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();
        try{
            if (!$apply->save()) {
                throw new Exception('申请失败',$apply->errors);
            }
            $this->approvalPerson($apply);
            $this->copyPerson($apply);
            $this->saveLoan($apply);
            $transaction->commit();
        } catch (Exception $exception){
            $transaction->rollBack();
            throw $exception;
        }
        return $apply->apply_id;
    }

    /**
     * 保存借款信息
     *
     * @param $apply
     * @return JieKuan
     * @throws Exception
     */
    public function saveLoan($apply)
    {
        $model = new JieKuan();
        $model->apply_id = $apply->apply_id;
        $model->bank_name = $this->bank_name;
        $model->bank_card_id = $this->bank_card_id;
        $model->bank_name_des = $this->bank_name_des ? : '';
        $model->pics = $this->pics ? : '';
        $model->money = $this->money;
        $model->des = $this->des;
        $model->tips = $this->tips;
        $model->get_money_time = 0;
        $model->pay_back_time = 0;
        $model->is_pay_back = 0;
        $model->status = 1;
        if (!$model->save()) {
            throw new Exception('借款保存失败', $model->errors);
        }
        return $model;
    }

}