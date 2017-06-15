<?php
namespace app\modules\oa_v1\models;

use yii;
use app\models\ApplyPositive;
use app\models\Apply;
use app\modules\oa_v1\logic\PersonLogic;
use yii\db\Exception;

class ApplyPositiveForm extends BaseForm
{
	public $type = 10;
	public $cai_wu_need = 1;
	
	public $apply_id;
	public $prosecution;
	public $summary;
	public $suggest;
	public $entry_time;
	public $job;
	public $files;
	public $approval_persons;
	public $copy_person;
	
	public function rules()
	{
		return [
				[
					['apply_id', 'prosecution', 'summary', 'suggest', 'entry_time', 'job','approval_persons'],
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
				['apply_id', 'unique','targetClass'=>'\app\models\Apply', 'message'=> '申请单已存在'],
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
			$this->savePositive($apply);
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
	 * 保存转正表
	 */
	public function savePositive($apply)
	{
		$model = new ApplyPositive();
		$model->apply_id = $this->apply_id;
		$model->prosecution = $this->prosecution;
		$model->summary = $this->summary;
		$model->suggest = $this->suggest;
		$model->entry_time = $this->entry_time;
		$model->org = PersonLogic::instance()->getOrgNameByPersonId($apply->person_id);
		$model->job = $this->job;
		$model->files = $this->files?json_encode($this->files):'';
		$model->created_at = time();
		if(!$model->save()){
			throw new Exception(current($model->getFirstErrors()));
		}
		
	}
}