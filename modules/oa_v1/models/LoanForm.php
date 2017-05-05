<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/5
 * Time: 11:10
 */

namespace app\modules\oa_v1\models;
use app\logic\PersonLogic;
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
     * 申请类型
     * @var
     */
    public $type;

    /**
     * 表单验证
     * @return array
     */
    public function rules()
    {
        return [
            [
                ['type'],
                'integer'
            ],
            [
                ['money', 'bank_card_id', 'bank_name', 'des', 'approval_persons', 'copy_person'],
                'required',
                'message' => '缺少必填字段'
            ],
            [
                ['approval_persons', 'copy_person'],
                'each',
                'rule' => ['integer']
            ],
            [
                ['pics'],
                'each',
                'rule' => ['safe']
            ],
            [
                ['money', 'bank_card_id', 'bank_name', 'bank_name_des','des', 'tips'],
                'string'
            ]
        ];
    }

    public function save()
    {
        $nextName = PersonLogic::instance()->getPersonName($this->approval_persons[0]);
        $user = ['person_id' => 1, 'person_name' => '测试'];
        $apply = new Apply();
        $apply->apply_id = $this->createApplyId();
        $apply->title = $this->title;
        $apply->create_time = $_SERVER['REQUEST_TIME'];
        $apply->type = $this->type;
        $apply->person_id = $user['person_id'];
        $apply->person = $user['person_name'];
        $apply->status = 1;
        $apply->next_des = '等待'.$nextName.'审批';
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();
        try{
            if (!$apply->save()) {
                new Exception('申请失败');
            }
            $this->approvalPerson($apply);
            $this->copyPerson($apply);
            $this->saveLoan($apply);
            $transaction->commit();

        } catch (Exception $exception){
            $transaction->rollBack();
            throw $exception;
        }
        return $this;
    }

    /**
     *
     * @param Apply $apply
     * @return JieKuan
     */
    public function saveLoan($apply)
    {
        $model = new JieKuan();
        $model->apply_id = $apply->apply_id;
        $model->bank_name = $this->bank_name;
        $model->bank_card_id = $this->bank_card_id;
        $model->bank_name_des = $this->bank_name_des;
        $model->pics = $this->pics;
        $model->money = $this->money;
        $model->des = $this->des;
        $model->tips = $this->tips;
        $model->status = 1;
        if (!$model->save()) {
            new Exception('借款保存失败');
        }
        return $model;
    }

}