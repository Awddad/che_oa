<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/13
 * Time: 10:39
 */

namespace app\modules\oa_v1\models;


use app\models\Apply;
use app\models\ApplyPay;
use app\models\Person;
use app\modules\oa_v1\logic\PersonLogic;
use Yii;
use yii\db\Exception;

/**
 * 申请付款表单
 *
 * Class ApplyPayForm
 * @package app\modules\oa_v1\models
 */
class ApplyPayForm extends BaseForm
{
    /**
     * 是否需要财务确认
     * @var
     */
    public $cai_wu_need = 2;
    
    /**
     * 申请ID
     * @var
     */
    public $apply_id;
    
    /**
     *
     * @var int
     */
    public $type = 4;
    
    public $money;
    
    public $to_name;
    
    public $bank_card_id;
    
    public $bank_name;
    
    public $files;
    
    public $des;
    
    public $pay_type = 0;
    
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
    
    
    public function rules()
    {
        return [
            [
                ['money', 'bank_card_id', 'bank_name', 'des', 'approval_persons', 'apply_id', 'to_name'],
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
            ['files', 'safe'],
            ['pay_type', 'integer'],
            ['des', 'string'],
            ['apply_id', 'checkOnly']
        ];
    }
    
    /**
     * @param Person $user
     * @return mixed
     * @throws Exception
     */
    public function save($user)
    {
        $apply = $this->setApply($user);
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$apply->save()) {
                throw new Exception('付款申请单创建失败');
            }
            $this->saveApplyPay();
            $this->approvalPerson($apply);
            $this->copyPerson($apply);
            $transaction->commit();
            return $apply;
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
    
    /**
     * 创建付款申请
     * @throws Exception
     */
    public function saveApplyPay()
    {
        $applyPay =  new ApplyPay();
        $applyPay->apply_id = $this->apply_id;
        $applyPay->bank_card_id = $this->bank_card_id;
        $applyPay->bank_name = $this->bank_name;
        $applyPay->money = $this->money;
        $applyPay->created_at = time();
        $applyPay->files = $this->files ? json_encode($this->files): '';
        $applyPay->des = $this->des;
        $applyPay->pay_type = $this->pay_type;
        $applyPay->to_name = $this->to_name;
        if (!$applyPay->save()) {
            throw new Exception('付款申请创建失败');
        }
        return true;
    }
}