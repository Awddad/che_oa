<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/14
 * Time: 14:36
 */

namespace app\modules\oa_v1\models;

use app\models\ApplyDemand;
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
            ['files', 'string'],
            ['apply_id', 'checkOnly'],
            ['demand_list', 'checkDemandList']
        ];
    }
    
    
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
     * @param User $user
     * @return Apply
     * @throws \Exception
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
        $model->files = $this->files;
        $model->des = $this->des;
        $model->status = 0;
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
                'apply_id' => $this->apply_id,
                'name' => $v['name'],
                'amount' => $v['amount'],
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
}