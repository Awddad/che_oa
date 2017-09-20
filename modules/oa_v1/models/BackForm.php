<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/9
 * Time: 11:01
 */

namespace app\modules\oa_v1\models;

use app\models\Apply;
use app\models\JieKuan;
use app\models\PayBack;
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
 * @property string $apply_id
 */
class BackForm extends BaseForm
{
    public $cai_wu_need = 2;
    
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
     * 申请ID
     * @var
     */
    public $apply_id;

    /**
     * 表单验证
     *
     * @return array
     */
    public function rules()
    {
        return [
            [
                ['bank_card_id', 'bank_name', 'apply_ids', 'approval_persons', 'apply_id'],
                'required'
            ],
            [
                ['bank_card_id', 'bank_name', 'bank_name_des', 'des', 'apply_id'],
                'string'
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
                ['apply_ids'],
                'checkApplyIds',
            ],
            ['apply_id', 'checkOnly']
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
        $apply = $this->setApply($user);
        
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
                JieKuan::updateAll(['status' => 100], ['apply_id' => $apply_id]);
            }
            $this->afterApplySave($apply);
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
            throw new Exception('还款保存失败', $model->errors);
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
                'money' => \Yii::$app->formatter->asCurrency($back->money),
                'detail' => $back->des
            ];
        }

        return $data;
    }
}