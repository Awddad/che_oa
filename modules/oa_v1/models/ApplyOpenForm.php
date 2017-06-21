<?php
namespace app\modules\oa_v1\models;

use yii;
use app\modules\oa_v1\logic\PersonLogic;
use app\models\Apply;
use app\models\ApplyOpen;
use Exception;

class ApplyOpenForm extends BaseForm
{
    public $type = 13;
    public $cai_wu_need = 1;
    
    
    public $apply_id;
    public $district;
    public $address;
    public $rental;
    public $summary;
    public $approval_persons;
    public $copy_person;
    public $files;
    
    public $district_type = 3;//district对应region的type值
    
    public function rules()
    {
        return [
            [
                ['apply_id','district','address','rental','summary','approval_persons'],
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
            ['rental','number','numberPattern'=>'/(^[1-9]([0-9]+)?(\.[0-9]{1,2})?$)|(^(0){1}$)|(^[0-9]\.[0-9]([0-9])?$)/','message' => '租金不正确！'],
            ['summary','string','max'=>250, 'message'=>'说明不正确！'],
            ['address','string','max'=>20, 'message'=>'地址不正确！'],
            ['district','exist','targetClass'=>'\app\models\Region','targetAttribute'=>['district'=>'id','district_type'=>'type'],'message'=>'区号不正确！'],
            ['apply_id', 'unique','targetClass'=>'\app\models\Apply', 'message'=> '申请单已存在'],
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
            $this->saveOpen($apply);
            $this->approvalPerson($apply);
            $this->copyPerson($apply);
            $transaction->commit();
            return ['status'=>true,'apply_id'=>$this->apply_id];
        }catch(Exception $e){
            $transaction->rollBack();
            return ['status'=>false,'msg'=>$e->getMessage()];
        }
    }
    
    public function saveOpen($apply)
    {
        $model = new ApplyOpen();
        $model->apply_id = $this->apply_id;
        $model->district = $this->district;
        $model->address = $this->address;
        $model->rental = $this->rental;
        $model->files = $this->files?json_encode($this->files):'';
        $model->summary = $this->summary;
        $model->created_at = time();
        if(!$model->save()){
            throw new Exception(current($model->getFirstErrors()));
        }
        
    }
}