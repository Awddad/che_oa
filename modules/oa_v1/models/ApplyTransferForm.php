<?php
namespace app\modules\oa_v1\models;

use yii;
use app\models\Apply;
use app\modules\oa_v1\logic\PersonLogic;
use yii\db\Exception;
use app\models\ApplyTransfer;

class ApplyTransferForm extends BaseForm
{
	public $type = 10;
	public $cai_wu_need = 1;
	
	public $apply_id;
	public $approval_persons;
	public $copy_person;
	public $old_org_id;
	public $old_profession;
	public $target_org_id;
	public $target_profession;
	public $entry_time;
	public $transfer_time;
	public $files;
	
	
	public function rules()
	{
		return [
				[
					['apply_id','old_org_id','old_profession','target_org_id','target_profession','entry_time','transfer_time'],
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
				['entry_time','date','format' => 'yyyy-mm-dd','message' => '入职时间不正确'],
				['transfer_time','date','format' => 'yyyy-mm-dd','message' => '调职时间不正确'],
				['apply_id', 'unique','targetClass'=>'\app\models\Apply', 'message'=> '申请单已存在'],
				['old_org_id','exist','targetClass'=>'\app\models\Org', 'targetAttribute'=>'org_id','message'=>'所属部门不存在'],
				['target_org_id','exist','targetClass'=>'\app\models\Org', 'targetAttribute'=>'org_id','message'=>'调职后部门不存在'],
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
			$this->saveTransfer();
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
	public function saveTransfer()
	{
		$model = new ApplyTransfer();
		$model->apply_id = $this->apply_id;
		$model->old_org_id = $this->old_org_id;
		$model->old_profession = $this->old_profession;
		$model->target_org_id = $this->target_org_id;
		$model->target_profession = $this->target_profession;
		$model->entry_time = $this->entry_time;
		$model->transfer_time = $this->transfer_time;
		$model->files = $this->files?json_encode($this->files):'';
		$model->created_at = time();
		
		if(!$model->save()){
			throw new Exception(current($model->getFirstErrors()));
		}
		
	}
}