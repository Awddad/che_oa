<?php
/**
 * Created by PhpStorm.
 * User: YJR
 * Date: 2017/9/25
 * Time: 10:56
 */
namespace app\modules\oa_v1\models;


use app\models\Apply;
use app\models\ApplyRetire;
use app\models\Person;
use yii\db\Exception;

class ApplyRetireForm extends BaseForm
{
    const SCENARIO_APPLY = 'apply';//申请

    public $type = 19;
    public $cai_wu_need = 1;

    public $person_id;
    public $des;
    public $date;
    public $files;

    public $person_status = 0;

    public function rules()
    {
        return [
            [
                ['apply_id','approval_persons','person_id','des','date'],
                'required',
                'on' => self::SCENARIO_APPLY,
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
            ['apply_id', 'unique','targetClass'=>'\app\models\Apply', 'message'=> '申请单已存在'],
            ['files','safe'],
            ['date','date','format' => 'yyyy-mm-dd','message' => '辞退时间不正确'],
            ['des','string','max' => 1024,'message' => '辞退说明不正确！'],
            ['person_id','exist','targetClass'=>'\app\models\Person', 'targetAttribute'=>['person_id'=>'person_id','person_status'=>'is_delete'],'message'=>'被辞退人不存在']
        ];
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_APPLY => ['apply_id','approval_persons','person_id','des','date','copy_person','files'],
        ];
    }

    public function saveApply($user)
    {
        $apply = $this->setApply($user);

        $transaction = \Yii::$app->db->beginTransaction();
        try{
            if(!$apply->save()){
                throw new Exception(current($apply->getFirstErrors()));
            }
            $this->saveRetire($apply);
            $this->approvalPerson($apply);
            $this->copyPerson($apply);
            $transaction->commit();
            $this->afterApplySave($apply);
            return ['status'=>true,'apply_id'=>$this->apply_id];
        }catch(\Exception $e){
            $transaction->rollBack();
            return ['status'=>false,'msg'=>$e->getMessage()];
        }
    }

    /**
     * @param $apply Apply
     */
    public function saveRetire($apply)
    {
        $person = Person::findOne($this->person_id);
        $model = new ApplyRetire();
        $model->apply_id = $apply->apply_id;
        $model->person_id = $this->person_id;
        $model->person_name = $person ? $person->person_name : '';
        $model->profession = $person ? $person->profession : '';
        $model->tel = $person ? $person->phone : '';
        $model->des = $this->des;
        $model->files = $this->files?json_encode($this->files):'';
        $model->created_at = time();

        if(!$model->save()){
            throw new Exception(current($model->getFirstErrors()));
        }
    }
}