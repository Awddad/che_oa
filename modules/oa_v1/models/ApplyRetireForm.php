<?php
/**
 * Created by PhpStorm.
 * User: YJR
 * Date: 2017/9/25
 * Time: 10:56
 */
namespace app\modules\oa_v1\models;

use Yii;
use app\models\Apply;
use app\models\ApplyRetire;
use app\models\Employee;
use app\models\Person;
use app\modules\oa_v1\logic\EmployeeLogic;
use yii\db\Exception;

class ApplyRetireForm extends BaseForm
{
    const SCENARIO_APPLY = 'apply';//申请
    const SCENARIO_EXECUTE = 'execute';//处理

    public $type = 19;
    public $cai_wu_need = 1;

    public $person_id;
    public $des;
    public $date;
    public $files;

    public $stock_status;
    public $finance_status;
    public $account_status;
    public $work_status;
    public $handover_id;

    public $person_status = 0;
    public $apply_status = 99;

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
                ['apply_id','finance_status','account_status','work_status','handover_id','des','date'],
                'required',
                'on' => self::SCENARIO_EXECUTE,
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
            ['apply_id', 'unique','targetClass'=>'\app\models\Apply', 'message'=> '申请单已存在','on' => self::SCENARIO_APPLY,],
            ['apply_id', 'exist','targetClass'=>'\app\models\ApplyRetire', 'message'=> '申请单不存在','on' => self::SCENARIO_EXECUTE,],
            ['files','safe'],
            ['date','date','format' => 'yyyy-mm-dd','message' => '辞退时间不正确'],
            ['des','string','max' => 1024,'message' => '辞退说明不正确！'],
            ['person_id','checkPersonId'],
            [
                'handover_id',
                'exist',
                'targetClass'=>'\app\models\Person',
                'targetAttribute'=>['handover_id'=>'person_id','person_status'=>'is_delete'],
                'message'=>'交接人不存在'
            ],
            [
                ['stock_status','finance_status','account_status','work_status'],
                'in',
                'range' => [0,1],
                'message' => '状态不正确'
            ]
        ];
    }

    public function checkPersonId($attr)
    {
        $data = Apply::find()->alias('a')
            ->leftJoin(ApplyRetire::tableName().' r','a.apply_id=r.apply_id')
            ->where(['a.type'=>19,'r.person_id'=>$this->$attr,'a.status'=>[1,11,99]])
            ->all();
        if($data){
            $this->addError($attr,'此人不能再申请辞退');
            return false;
        }else{
            $person = Person::findOne(['person_id'=>$this->$attr,'is_delete'=>0]);
            if(!$person){
                $this->addError($attr,'被辞退人不存在');
                return false;
            }
        }
        return true;
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_APPLY => ['apply_id','approval_persons','person_id','des','date','copy_person','files'],
            self::SCENARIO_EXECUTE => ['apply_id','stock_status','finance_status','account_status','work_status','handover_id','des','date'],
        ];
    }

    public function saveApply($user)
    {
        $apply = $this->setApply($user);

        $transaction = Yii::$app->db->beginTransaction();
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
     * @throws Exception
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
        $model->retire_date = $this->date;
        $model->des = $this->des;
        $model->files = $this->files?json_encode($this->files):'';
        $model->created_at = time();

        if(!$model->save()){
            throw new Exception(current($model->getFirstErrors()));
        }
    }

    /**
     * @param $user Person
     * @return array
     */
    public function execute($user)
    {
        $apply = Apply::find()->where(['apply_id'=>$this->apply_id,'status'=>99,'type'=>19]);
        if(empty($apply)){
            return ['status'=>false,'msg'=>'申请不存在 或未审批完成'];
        }
        /**
         * @var $retire ApplyRetire
         */
        $retire = ApplyRetire::find()->where(['apply_id'=>$this->apply_id,'is_execute'=>0])->one();
        if($retire) {
            $retire->is_execute = 1;
            $retire->execute_person_id = $user->person_id;
            $retire->execute_person = $user->person_name;
            $retire->account_status = $this->account_status;
            $retire->work_status = $this->work_status;
            $retire->finance_status = $this->finance_status;
            $retire->stock_status = $this->stock_status;
            $retire->leave_des = $this->des;
            $retire->leave_time = $this->date;
            $retire->handover_person_id = $this->handover_id;
            $retire->handover = Person::findOne($this->handover_id)->person_name;
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if (!$retire->save()) {
                    throw new Exception(current($retire->getFirstErrors()));
                }
                $this->leave($retire);
                $transaction->commit();
                return ['status' => true];
            }catch(\Exception $e){
                $transaction->rollBack();
                return ['status'=>false,'msg'=>$e->getMessage()];
            }
        }
        return ['status'=>false,'msg'=>'申请已处理'];
    }

    /**
     * @param $retire ApplyRetire
     * @return bool
     * @throws Exception
     */
    private function leave($retire)
    {
        $emp = Employee::findOne(['person_id' => $retire->person_id]);
        if ($emp) {
            $emp->status = 3;
            $emp->leave_time = $retire->leave_time;
            if ($emp->save()) {
                //权限系统接口
                $res = EmployeeLogic::instance()->delQxEmp($emp);
                if(!$res['status']){
                    throw new Exception($res['msg']);
                }else{
                    return true;
                }
            }else{
                throw new Exception(current($emp->getFirstErrors()));
            }
        }else{
            throw new Exception('员工不存在');
        }
    }
}