<?php
namespace app\modules\oa_v1\models;

use yii;
use app\models\ApplyPositive;
use app\models\Apply;
use app\modules\oa_v1\logic\PersonLogic;
use yii\db\Exception;
use app\models\Job;

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
	
	public $positive_time;
	
	public function rules()
	{
		return [
		          [
		              ['apply_id', 'prosecution', 'summary', 'suggest', 'entry_time', 'job','approval_persons','positive_time'],
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
				  ['positive_time','date','format' => 'yyyy-mm-dd','message' => '转正时间不正确'],
		          ['job','exist','targetClass'=>'\app\models\Job','targetAttribute'=>'id','message'=>'职位不存在'],
				  ['apply_id', 'unique','targetClass'=>'\app\models\Apply', 'message'=> '申请单已存在'],
				  ['files','safe'],
		];
	}
	
	public function save($user)
	{
        $apply = $this->setApply($user);
		
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
		$model->org_id = $apply->org_id;
		$model->org = PersonLogic::instance()->getOrgNameByPersonId($apply->person_id);
		$model->profession_id = $this->job;
		$model->profession = Job::findOne($this->job)->name;
		$model->positive_time = $this->positive_time;
		$model->files = $this->files?json_encode($this->files):'';
		$model->created_at = time();
		if(!$model->save()){
			throw new Exception(current($model->getFirstErrors()));
		}
		
	}
}