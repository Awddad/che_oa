<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/14
 * Time: 14:36
 */

namespace app\modules\oa_v1\models;

use app\models\ApplyBuy;
use app\models\ApplyDemand;
use app\models\Person;
use Yii;
use app\models\Apply;
use app\models\User;
use app\modules\oa_v1\logic\PersonLogic;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * 请购单
 *
 * Class ApplyDemandForm
 * @package app\modules\oa_v1\models
 */
class ApplyDemandForm extends BaseForm
{
    const CONFIRM_BUY = 'confirm_buy';
    /**
     * 申请ID
     * @var
     */
    public $apply_id;
    
    /**
     *
     * @var int
     */
    public $type = 6;
    
    public $files = '';
    
    public $des = '';
    
    /**
     * 审批人
     * @var array
     */
    public $approval_persons = [];
    
    public $demand_list = [];
    
    /**
     * 抄送人
     * @var array
     */
    public $copy_person = [];
    
    public $buy_type;
    
    public $apply_buy_id;
    
    public $tips = '';
    
    
    public function rules()
    {
        return [
            [
                [ 'approval_persons', 'apply_id', 'demand_list'],
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
            [['des', 'tips'], 'string'],
            ['files', 'safe'],
            ['apply_id', 'checkOnly', 'on' => ['default']],
            ['demand_list', 'checkDemandList'],
            [['apply_id', 'buy_type', 'apply_buy_id',],  'required', 'on' => [self::CONFIRM_BUY] ],
            ['apply_buy_id', 'checkApplyBuyId']
        ];
    }
    
    /**
     * 设置场景
     *
     * @return array
     */
    public function scenarios()
    {
        return [
            self::CONFIRM_BUY => ['apply_id', 'buy_type', 'apply_buy_id', 'tips'],
            'default' => ['approval_persons', 'apply_id', 'demand_list', 'copy_person', 'des', 'files']
        ];
    }
    
    /**
     * 检查请购单是否存在
     *
     * @param $attribute
     */
    public function checkApplyBuyId($attribute)
    {
        if ($this->scenario == self::CONFIRM_BUY) {
            if ($this->$attribute == 0 && $this->buy_type != 3) {
                $this->addError($attribute, '请购单不存在');
            }
            if ($this->$attribute > 0) {
                $applyBuy = Apply::findOne($this->$attribute);
                if (!$applyBuy  || $applyBuy->status != 99 || $applyBuy->type != 5 ) {
                    $this->addError($attribute, '请购单不存在或者未审核通过');
                }
    
            }
        }
    }
    
    /**
     * 检查$demand_list
     *
     * @param $attribute
     */
    public function checkDemandList($attribute)
    {
        if (!is_array($this->$attribute)) {
            $this->addError($attribute, '请购明细格式错误');
        }
        foreach ($this->$attribute as $value) {
            if(!ArrayHelper::getValue($value, 'name')) {
                $this->addError($attribute, '需求明细名称格式错误');
            }
            $amount = ArrayHelper::getValue($value, 'amount');
            if(!$amount || !is_numeric($amount)) {
                $this->addError($attribute, '需求明细数量为正整数');
            }
        }
    }
    
    /**
     * @param Person $user
     * @return Apply
     * @throws \Exception
     */
    public function save($user)
    {
        $apply = $this->setApply($user);
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$apply->save()) {
                throw new Exception('付款申请单创建失败');
            }
            $this->saveApplyDemand();
            $this->approvalPerson($apply);
            $this->copyPerson($apply);
            $this->saveApplyDemandList();
            $transaction->commit();
            return $apply;
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
    
    /**
     *
     * 保存需求单
     * @return ApplyDemand
     * @throws Exception
     */
    public function saveApplyDemand()
    {
        $model = new ApplyDemand();
        $model->apply_id = $this->apply_id;
        $model->files = $this->files ? json_encode($this->files): '';
        $model->des = $this->des;
        $model->status = 1;
        if (!$model->save()) {
            throw new Exception('需求单保存失败');
        }
        return $model;
    }
    
    /**
     * 保存请购单列表
     * @throws Exception
     */
    public function saveApplyDemandList()
    {
        $data = [];
        foreach ($this->demand_list as $v) {
            $data[] = [
                $this->apply_id,
                $v['name'],
                $v['amount'],
            ];
        }
        $n = Yii::$app->db->createCommand()->batchInsert('oa_apply_demand_list', [
            'apply_id', 'name', 'amount',
        ], $data)->execute();
        if(!$n) {
            throw new Exception('请购明细保存失败');
        }
        return true;
    }
    
    /**
     * 采购单确认
     *
     * @return object $apply
     */
    public function confirmSave()
    {
        $apply = ApplyDemand::findOne($this->apply_id);
        $apply->buy_type = $this->buy_type;
        if ($this->buy_type == 2) {
            $apply->status = 2;
        } else {
            $apply->status = 3;
        }
        $apply->apply_buy_id = $this->apply_buy_id;
        $apply->tips = $this->tips;
        if (!$apply->save()) {
            return $apply;
        }
        return $apply;
    }
}