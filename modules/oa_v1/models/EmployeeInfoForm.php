<?php
namespace app\modules\oa_v1\models;

use app\modules\oa_v1\logic\PeopleLogic;
use app\models\Job;
use yii\helpers\ArrayHelper;
use app\models\Employee;
use app\models\Region;
use app\models\Political;

class EmployeeInfoForm extends BaseForm
{
    const SCENARIO_EMP_EDIT = 'emp_edit';//员工个人信息修改
    
    public $empno;
     
    public $id;    
    public $name;    
    public $sex;    
    public $phone;    
    public $birthday;    
    public $email;    
    public $age;    
    public $nation;    
    public $edu;    
    public $political;    
    public $native;    
    public $work_time;    
    public $marriage;    
    public $location; 
    public $id_card;
    public $profession;
    public $entry_time;
    public $emp_type;
    public $org_id;
    
    public function rules()
    {
        return [
            [
                ['id','org_id','name','empno','sex','phone','birthday','email','age','edu','work_time','location','id_card','entry_time','emp_type'],
                'required',
                'on' => [self::SCENARIO_EMP_EDIT],
                'message' => '{attribute}不能为空'
            ],
            
            ['id','exist','targetClass'=>'\app\models\Employee','message'=>'员工不存在','on'=>[self::SCENARIO_EMP_EDIT]],
            ['profession','exist','targetClass'=>'\app\models\Job','targetAttribute'=>'id','message'=>'职位不存在'],
            ['sex','in', 'range' => [1, 2], 'message'=>'性别不正确'],
            ['phone','match','pattern'=>'/^1\d{10}/','message'=>'手机号不正确'],
            ['birthday','date','format' => 'yyyy-mm-dd','message' => '生日时间不正确'],
            ['entry_time','date','format' => 'yyyy-mm-dd','message' => '入职时间不正确'],
            ['email','email','message'=>'email不正确'],
            ['age', 'integer','message'=>'年龄不正确'],
            ['nation','string','max'=>15],//民族
            ['edu','exist','targetClass'=>'\app\models\Educational','targetAttribute'=>'id','message'=>'学历不存在'],
            ['political','exist','targetClass'=>'\app\models\Political','targetAttribute'=>'id','message'=>'政治面貌不存在'],
            ['native','string','max'=>15],//籍贯
            ['work_time','integer','message'=>'工作年限不正确'],
            ['marriage','in','range'=>[0,1,2],'message'=>'婚姻状况不正确'],
            ['emp_type','exist','targetClass'=>'\app\models\EmployeeType','targetAttribute'=>'id','message'=>'员工类型不存在'],
            ['location','exist','targetClass'=>'\app\models\Region','targetAttribute'=>'id','message'=>'当前所在地不正确！','on'=>[self::SCENARIO_EMP_EDIT]],
            ['org_id','exist','targetClass'=>'\app\models\Org','message'=>'组织不存在'],
        ];
    }
    
    public function scenarios()
    {
        return [
            self::SCENARIO_EMP_EDIT => ['id','name','empno','sex','phone','birthday','email','age','nation','edu','political','native','work_time','marriage','location','id_card','entry_time','emp_type'],
            
        ];
    }
    /**
     * 保存员工基本信息
     * @param array $user
     */
    public function saveEmployee($user)
    {
        $model = Employee::findOne($this->id);
        if(empty($model)){
            return ['status'=>false,'msg'=>'员工不存在'];
        }
        $model->org_id = $this->org_id;
        $model->name = $this->name;
        $model->empno = $this->empno;
        $model->profession = $this->profession;
        $model->id_card = $this->id_card;
        $model->entry_time = $this->entry_time;
        $model->employee_type = $this->emp_type;
        $model->sex = $this->sex;
        $model->phone = $this->phone;
        $model->email = $this->email;
        $model->birthday = $this->birthday;
        $model->age = $this->age;
        $model->work_time = $this->work_time;
        $model->educational = $this->edu;
        $model->current_location = $this->location;
        $this->native && $model->native = $this->native;
        $this->political && $model->political = $this->political;
        $this->nation && $model->nation = $this->nation;
        $this->marriage && $model->marriage = $this->marriage;
        if(!$model->save()){
            return ['status'=>false,'msg'=>current($this->getFirstErrors())]; 
        }else{
            PeopleLogic::instance()->addLog(0,$model->id,'编辑员工个人信息',ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }
    }
    
    
    /**
     * 获得员工个人信息
     * @param int $id
     * @return array
     */
    public function getEmpInfo($id)
    {
        $model = Employee::findOne($id);
        if(empty($model)){
            return ['status'=>false,'msg'=>'员工不存在'];
        }
        $data = [
            'id' => $model->id,
            'name' => $model->name,
            'empno' => $model->empno,
            'profession_id' => $model->profession,
            'profession' => empty($model->job)?'':$model->job->name,
            'org_id' => $model->org_id,
            'org' => empty($model->org)?'':$model->org->org_name,
            'id_card' => $model->id_card,
            'entry_time' => $model->entry_time,
            'emp_type_id' => $model->employee_type,
            'emp_type' => empty($model->employeeType)?'':$model->employeeType->name,
            'sex' => $model->sex,
            'phone' => $model->phone,
            'email' => $model->email,
            'birthday' => $model->birthday,
            'age' => $model->age,
            'work_time' => $model->work_time,
            'edu' => $model->educational,
            'location_id' => $model->current_location,
            'location' => ($region = Region::findOne($model->current_location)) ? $region->name : '',
            'native' => $model->native,
            'political_id' => $model->political,
            'political' => ($tmp = Political::findOne($model->political)) ? $tmp->political : '',
            'nation' => $model->nation,
            'marriage' => $model->marriage
        ];
        return ['status'=>true,'data'=>$data];
    }
    
    
}