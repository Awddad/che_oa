<?php
namespace app\modules\oa_v1\models;

use yii;
use app\models\ApplyLeave;
use app\modules\oa_v1\logic\PersonLogic;
use app\models\Apply;
use Exception;
use app\models\Person;

class ApplyLeaveForm extends BaseForm
{
    public $type = 11;
    public $cai_wu_need = 1;
    
    public $apply_id;
    public $leave_time;
    public $des;
    public $stock_status;
    public $finance_status;
    public $account_status;
    public $work_status;
    public $files;
	public $approval_persons;
	public $copy_person;
	public $handover_id;
    
    public function rules()
    {
        return [
            [
                ['apply_id','leave_time','des','stock_status','finance_status','account_status','handover_id','work_status','approval_persons'],
                'required',
                'message'=>'{attribute}不能为空'
            ],
            [
                ['approval_persons', 'copy_person'],
                'each',
                'rule' => ['integer']
            ],
            [
                ['approval_persons', 'copy_person'], 'checkTotal'
            ],
            [['stock_status','finance_status','account_status','work_status'],'in', 'range' => [1, 0],'message'=>'{attribute}不正确！'],
            ['des','string','max' => 250,'message' => '离职说明不正确！'],
            ['leave_time','date','format' => 'yyyy-mm-dd','message' => '离职时间不正确'],
            ['apply_id', 'unique','targetClass'=>'\app\models\Apply', 'message'=> '申请单已存在'],
            ['handover_id','exist','targetClass'=>'\app\models\Person','targetAttribute'=>'person_id','message'=>'交接人不存在！'],
            ['files','safe'],
        ];
    }
    
    public function saveApply($user)
    {
        $applyId = $this->apply_id;
        $pdfUrl = '';
        $nextName = PersonLogic::instance()->getPersonName($this->approval_persons[0]);
        
        $apply = new Apply();
        $apply->apply_id = $applyId;
        $apply->title = $this->createApplyTitle($user);
        $apply->create_time = time();
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
        try{
            if(!$apply->save()){
                throw new Exception(current($apply->getFirstErrors()));
            }
            $this->saveLeave($apply);
            $this->approvalPerson($apply);
            $this->copyPerson($apply);
            $transaction->commit();
            return ['status'=>true,'apply_id'=>$this->apply_id];
        }catch(Exception $e){
            $transaction->rollBack();
            return ['status'=>false,'msg'=>$e->getMessage()];
        }
    }
    
    /**
     * 添加离职单
     */
    public function saveLeave($apply)
    {
        $model = new ApplyLeave();
        $model->apply_id = $apply->apply_id;
        $model->leave_time = $this->leave_time;
        $model->des = $this->des;
        $model->stock_status = $this->stock_status;
        $model->account_status = $this->account_status;
        $model->work_status = $this->work_status;
        $model->finance_status = $this->finance_status;
        $model->handover_person_id = $this->handover_id;
        $model->handover = Person::findOne($this->handover_id)->person_name;
        $model->files = $this->files?json_encode($this->files):'';
        $model->created_at = time();
        
        if(!$model->save()){
            throw new Exception(current($model->getFirstErrors()));
        }
    }
}