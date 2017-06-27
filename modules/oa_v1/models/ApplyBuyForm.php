<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/13
 * Time: 13:41
 */

namespace app\modules\oa_v1\models;

use app\models\ApplyBuy;
use app\modules\oa_v1\logic\AssetLogic;
use Yii;
use app\models\Apply;
use app\models\User;
use app\modules\oa_v1\logic\PersonLogic;
use yii\db\Exception;
use yii\helpers\ArrayHelper;


/**
 * 申请请购表单
 *
 * Class ApplyBuyForm
 * @package app\modules\oa_v1\models
 */
class ApplyBuyForm extends BaseForm
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
    public $type = 5;
    
    public $money;
    
    public $to_name;
    
    public $bank_card_id;
    
    public $bank_name;
    
    public $files;
    
    public $des;
    
    /**
     * 审批人
     * @var array
     */
    public $approval_persons = [];
    
    public $buy_list = [];
    
    /**
     * 抄送人
     * @var array
     */
    public $copy_person = [];
    
    public function rules()
    {
        return [
            [
                ['money', 'bank_card_id', 'bank_name', 'des', 'approval_persons', 'apply_id', 'to_name', 'buy_list'],
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
            ['des', 'string'],
            ['files', 'safe'],
            ['apply_id', 'checkOnly'],
            ['buy_list', 'checkBuyList']
        ];
    }
    
    /**
     * 检查请购明细
     *
     * @param $attribute
     */
    public function checkBuyList($attribute)
    {
        if (!is_array($this->$attribute)) {
            $this->addError($attribute, '请购明细格式错误');
        }
        foreach ($this->$attribute as $value) {
            if(!ArrayHelper::getValue($value, 'asset_type_id')) {
                $this->addError($attribute, '请购明细类别格式错误');
            }
            if(!ArrayHelper::getValue($value, 'asset_brand_id')) {
                $this->addError($attribute, '请购明细品牌错误');
            }
            if(!ArrayHelper::getValue($value, 'name')) {
                $this->addError($attribute, '请购明细名称格式错误');
            }
            if(!ArrayHelper::getValue($value, 'price')) {
                $this->addError($attribute, '请购明细价格格式错误');
            }
            $amount = ArrayHelper::getValue($value, 'amount');
            if(!$amount || !is_numeric($amount)) {
                $this->addError($attribute, '请购明细数量为正整数');
            }
        }
    }
    
    /**
     * @param User $user
     * @return mixed
     * @throws Exception
     */
    public function save($user)
    {
        $applyId = $this->apply_id;
        $pdfUrl = '';
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
        $apply->cai_wu_need = $this->cai_wu_need;
        $apply->org_id = $user['org_id'];
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$apply->save()) {
                throw new Exception('付款申请单创建失败');
            }
            $this->saveApplyBuy();
            $this->approvalPerson($apply);
            $this->copyPerson($apply);
            $this->saveApplyBuyList();
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
    public function saveApplyBuy()
    {
        $applyPay =  new ApplyBuy();
        $applyPay->apply_id = $this->apply_id;
        $applyPay->bank_card_id = $this->bank_card_id;
        $applyPay->bank_name = $this->bank_name;
        $applyPay->money = $this->money;
        $applyPay->files = $this->files ? json_encode($this->files): '';
        $applyPay->des = $this->des;
        $applyPay->to_name = $this->to_name;
        if (!$applyPay->save()) {
            throw new Exception('付款申请创建失败');
        }
        return true;
    }
    
    /**
     * 请购明细
     *
     * @throws Exception
     */
    public function saveApplyBuyList()
    {
        $data = [];
        foreach ($this->buy_list as $v) {
            $data[] = [
                $this->apply_id,
                $v['asset_type_id'],
                AssetLogic::instance()->getAssetType($v['asset_type_id']),
                $v['asset_brand_id'],
                AssetLogic::instance()->getAssetType($v['asset_brand_id']),
                $v['name'],
                $v['price'],
                $v['amount'],
            ];
        }
        if($data) {
            $n = Yii::$app->db->createCommand()->batchInsert('oa_apply_buy_list', [
                'apply_id', 'asset_type_id', 'asset_type_name','asset_brand_id', 'asset_brand_name','name', 'price', 'amount',
            ], $data)->execute();
            if (!$n) {
                throw new Exception('请购明细保存失败');
            }
        } else {
            throw new Exception('请购明细不能为空');
        }
    }
}