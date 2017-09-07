<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/5
 * Time: 11:10
 */

namespace app\modules\oa_v1\models;

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
    public $files;

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
    
    public $cai_wu_need = 2;

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
                ['approval_persons', 'copy_person'], 'checkTotal'
            ],
            [
                ['files'], 'safe'
            ],
            [
                ['money', 'bank_card_id', 'bank_name', 'bank_name_des','des', 'tips', 'apply_id'],
                'string'
            ],
            ['apply_id', 'checkOnly']
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
        $apply = $this->setApply($user);
        
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
        $model->pics = $this->files ? json_encode($this->files): '';
        $model->money = $this->money;
        $model->des = $this->des;
        $model->tips = $this->tips;
        $model->get_money_time = 0;
        $model->pay_back_time = 0;
        $model->is_pay_back = 0;
        $model->status = 1;
        if (!$model->save()) {
            throw new Exception('备用金保存失败', $model->errors);
        }
        return $model;
    }

}