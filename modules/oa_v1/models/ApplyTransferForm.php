<?php
namespace app\modules\oa_v1\models;

use yii;
use app\models\Apply;
use app\modules\oa_v1\logic\PersonLogic;
use yii\db\Exception;
use app\models\ApplyTransfer;
use app\models\Job;
use app\models\Employee;
use app\models\Org;

class ApplyTransferForm extends BaseForm
{
	public $type = 12;
	public $cai_wu_need = 1;
	
	public $apply_id;
	public $approval_persons;
	public $copy_person;
	public $old_org_id;
	public $old_profession_id;
	public $target_org_id;
	public $target_profession_id;
	public $entry_time;
	public $transfer_time;
	public $files;
	public $des;
	
	
	public function rules()
	{
		return [
				[
					['apply_id',/*'old_org_id','old_profession_id',*/'target_org_id','target_profession_id','entry_time','transfer_time','des'],
					'required',
					'message' => '{attribute}不能为空'
				],
				[
					['approval_persons', 'copy_person'],
					'each',
					'rule' => ['integer']
					],
				[
					['approval_persons', 'copy_person'], 'checkTotal'
				],
				['des','string','max' => 1024,'message' => '调职原因不正确！'],
				['entry_time','date','format' => 'yyyy-mm-dd','message' => '入职时间不正确'],
				['transfer_time','date','format' => 'yyyy-mm-dd','message' => '调职时间不正确'],
				['apply_id', 'unique','targetClass'=>'\app\models\Apply', 'message'=> '申请单已存在'],
				['old_org_id','exist','targetClass'=>'\app\models\Org', 'targetAttribute'=>'org_id','message'=>'所属部门不存在'],
				['target_org_id','exist','targetClass'=>'\app\models\Org', 'targetAttribute'=>'org_id','message'=>'调职后部门不存在'],
		        ['old_profession_id','exist','targetClass'=>'\app\models\Job','targetAttribute'=>'id','message'=>'原职位不存在'],
		        ['target_profession_id','exist','targetClass'=>'\app\models\Job','targetAttribute'=>'id','message'=>'调职后职位不存在'],
				['files','safe'],
		];
	}
	
	public function save($user)
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
			$this->saveTransfer($user);
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
	 * 保存调职表
	 */
	public function saveTransfer($user)
	{
	    $emp = Employee::findOne(['person_id'=>$user['person_id']]);
	    if(empty($emp)){
	        throw new Exception('员工不存在');
	    }
		$model = new ApplyTransfer();
		$model->apply_id = $this->apply_id;
		/* 员工原部门原职位自己带出 
		$model->old_org_id = $this->old_org_id;
		$model->old_org_name = PersonLogic::instance()->getOrgById($this->old_org_id);
		$model->old_profession_id = $this->old_profession_id;
		$model->old_profession = Job::findOne($this->old_profession_id)->name;
		*/
		$model->old_org_id = $emp->org_id;
		$model->old_org_name = ($o_org = Org::findOne($emp->org_id)) ?$o_org->org_name:'';
		$model->old_profession_id = $emp->profession;
		$model->old_profession = Job::findOne($emp->profession)->name;
		
		$model->target_org_id = $this->target_org_id;
		$model->target_org_name = ($t_org = Org::findOne($this->target_org_id)) ?$t_org->org_name:'';
		$model->target_profession_id = $this->target_profession_id;
		$model->target_profession = Job::findOne($this->target_profession_id)->name;
		$model->entry_time = $this->entry_time;
		$model->transfer_time = $this->transfer_time;
		$model->des = $this->des;
		$model->files = $this->files?json_encode($this->files):'';
		$model->created_at = time();
		
		if(!$model->save()){
			throw new Exception(current($model->getFirstErrors()));
		}
		
	}
}