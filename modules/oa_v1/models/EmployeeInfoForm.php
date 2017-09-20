<?php
namespace app\modules\oa_v1\models;

use app\modules\oa_v1\logic\PeopleLogic;
use yii\helpers\ArrayHelper;
use app\models\Employee;
use app\models\Political;
use app\models\EmployeeAccount;
use app\models\PersonBankInfo;
use yii;
use app\modules\oa_v1\logic\EmployeeLogic;
use app\models\Educational;
use app\modules\oa_v1\logic\RegionLogic;
use app\modules\oa_v1\logic\OrgLogic;
use app\models\Org;
use yii\db\Exception;

class EmployeeInfoForm extends BaseForm
{
    const SCENARIO_EMP_EDIT = 'emp_edit';//员工个人信息修改
    const SCENARIO_EMP_ACCOUNT_EDIT = 'emp_account_edit';//员工帐号信息修改
    const SCENARIO_EMP_BANK_EDIT = 'emp_bank_edit';//员工银行卡修改
    const SCENARIO_EMP_BANK_DEL = 'emp_bank_del';//员工银行卡删除
    const SCENARIO_EMP_SERVICE_EDIT = 'emp_service_edit';//员工劳动关系编辑
    
    public $arr_status = [
        '0' => '待入职',
        '1' => '取消入职',
        '2' => '已入职',
        '3' => '已离职',
        //'4' => '再入职'
    ];
    
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
    
    public $qq;
    public $bank_name;
    public $bank_des;
    public $card_id;
    public $is_salary;
    public $bk_id;
    
    public $status;
    
    public $org_pid = 1;//公司pid
    
    public function rules()
    {
        return [
            [
                ['id','org_id','profession','name',/*'empno',*/'sex','phone','birthday','email','age','edu','work_time','location','id_card','entry_time','emp_type'],
                'required',
                'on' => [self::SCENARIO_EMP_EDIT],
                'message' => '{attribute}不能为空'
            ],
            [
                ['id','qq','email','phone'],
                'required',
                'on' => [self::SCENARIO_EMP_ACCOUNT_EDIT],
                'message' => '{attribute}不能为空'
            ],
            [
                ['id','bank_name','card_id','is_salary'],
                'required',
                'on' => [self::SCENARIO_EMP_BANK_EDIT],
                'message' => '{attribute}不能为空'
            ],
            [
                ['id','bk_id'],
                'required',
                'on' => [self::SCENARIO_EMP_BANK_DEL],
                'message' => '{attribute}不能为空'
            ],
            [
                ['id','org_id'],
                'required',
                'on' => [self::SCENARIO_EMP_SERVICE_EDIT],
                'message' => '{attribute}不能为空'
            ],
            ['id','exist','targetClass'=>'\app\models\Employee','message'=>'员工不存在'],
            ['profession','exist','targetClass'=>'\app\models\Job','targetAttribute'=>'id','message'=>'职位不存在'],
            ['sex','in', 'range' => [1, 2], 'message'=>'性别不正确'],
            ['phone','match','pattern'=>'/^1\d{10}/','message'=>'手机号不正确'],
            ['birthday','date','format' => 'yyyy-mm-dd','message' => '生日时间不正确'],
            //['entry_time','date','format' => 'yyyy-mm-dd','message' => '入职时间不正确'],
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
            ['org_id','exist','targetClass'=>'\app\models\Org','message'=>'组织不存在','on'=>[self::SCENARIO_EMP_EDIT]],
            ['org_id','exist','targetClass'=>'\app\models\Org','targetAttribute'=>['org_id'=>'org_id','org_pid'=>'pid'], 'message'=>'公司不正确','on'=>[self::SCENARIO_EMP_SERVICE_EDIT]],
            ['qq','string','max'=>20],
            ['bk_id','exist','targetClass'=>'\app\models\PersonBankInfo','targetAttribute'=>'id','message'=>'银行卡不存在'],
            ['marriage','in','range'=>[0,1,2,3],'message'=>'婚姻状况不正确'],
            ['status','checkStatus'],
        ];
    }
    
    public function checkStatus($attribute)
    {
        if(!$this->hasErrors()){
            $emp = Employee::findOne($this->id);
            if($emp->status == $this->$attribute || ($emp->status == 2 && in_array($this->$attribute,[2,3]))){
                return true;
            }else{
                $this->addError($attribute,'状态不正确');
                return false;
            }
        }
        return false;
    }
    
    public function scenarios()
    {
        return [
            self::SCENARIO_EMP_EDIT => ['id','org_id','profession','name',/*'empno',*/'sex','phone','birthday','email','age','nation','edu','political','native','work_time','marriage','location','id_card','entry_time','emp_type','status'],
            self::SCENARIO_EMP_ACCOUNT_EDIT => ['id','qq','email','phone'],
            self::SCENARIO_EMP_BANK_EDIT => ['id','bk_id','bank_name','bank_des','card_id','is_salary'],
            self::SCENARIO_EMP_BANK_DEL => ['id','bk_id'],
            self::SCENARIO_EMP_SERVICE_EDIT => ['id','org_id']
        ];
    }
    /**
     * 保存员工基本信息
     * @param array $user
     * @return array
     */
    public function saveEmployee($user)
    {
        $model = Employee::findOne($this->id);
        if(empty($model)){
            return ['status'=>false,'msg'=>'员工不存在'];
        }
        $model->org_id = $this->org_id;
        $model->name = $this->name;
        //$model->empno = $this->empno;
        $model->profession = $this->profession;
        $model->id_card = $this->id_card;
        $model->entry_time = $this->entry_time?:'';
        $model->employee_type = $this->emp_type;
        $model->sex = $this->sex;
        $model->phone = $this->phone;
        $model->email = $this->email;
        $model->birthday = $this->birthday;
        $model->age = $this->age;
        $model->work_time = $this->work_time;
        $model->educational = $this->edu;
        $model->current_location = $this->location;
        $this->status && $model->status = $this->status;
        $this->native && $model->native = $this->native;
        $this->political && $model->political = $this->political;
        $this->nation && $model->nation = $this->nation;
        $this->marriage && $model->marriage = $this->marriage;
        $transaction = Yii::$app->db->beginTransaction();
        try{
            if(!$model->save()){
                throw new Exception(current($this->getFirstErrors()));
            }
            if($model->status != 0 && $model->getOldAttribute('status') != 3){//未入职状态 已离职状态 不同步权限系统
                $fun = $model->status == 3 ? 'delQxEmp' : 'editQxEmp';
                $res = EmployeeLogic::instance()->$fun($model);
                if(!$res['status']){
                    throw new Exception($res['msg']);
                }
            }
            PeopleLogic::instance()->addLog(0,$model->id,'编辑员工个人信息',ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            $transaction->commit();
            return ['status'=>true];
        }catch(Exception $e){
            $transaction->rollBack();
            return ['status'=>false,'msg'=>$e->getMessage()];
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
        $data =  $this->getEmpInfoByEmployee($model);
        return ['status'=>true,'data'=>$data];
    }
    
    /**
     * @param $employee
     *
     * @return array
     */
    public function getEmpInfoByEmployee($employee)
    {
        $data = [
            'id' => $employee->id,
            'name' => $employee->name,
            'empno' => $employee->person_id,
            'profession_id' => $employee->profession,
            'profession' => empty($employee->job)?'':$employee->job->name,
            'org_id' => $employee->org_id,
            'org' => OrgLogic::instance()->getOrgName($employee->org_id),
            'org_info' => OrgLogic::instance()->getOrgIdByChild($employee->org_id),
            'id_card' => $employee->id_card,
            'entry_time' => $employee->entry_time,
            'emp_type_id' => $employee->employee_type,
            'emp_type' => empty($employee->employeeType)?'':$employee->employeeType->name,
            'sex' => $employee->sex,
            'phone' => $employee->phone,
            'email' => $employee->email,
            'birthday' => $employee->birthday,
            'age' => $employee->age,
            'work_time' => $employee->work_time,
            'edu_id' => $employee->educational,
            'edu' => ($edu = Educational::findOne($employee->educational)) ? $edu->educational : '',
            'location_id' => $employee->current_location,
            'location' => RegionLogic::instance()->getRegionByChild($employee->current_location),
            'location_info' => RegionLogic::instance()->getRegionIdByChild($employee->current_location),
            'native' => $employee->native,
            'political_id' => $employee->political,
            'political' => ($tmp = Political::findOne($employee->political)) ? $tmp->political : '',
            'nation' => $employee->nation,
            'entry' => $employee->status,
            'entry_status' => $this->arr_status[$employee->status],
            'marriage' => $employee->marriage
        ];
        return $data;
    }
    
    
    /**
     * 修改帐号
     * @param array $user
     * @return array
     */
    public function saveAccount($user)
    {
        $model = EmployeeAccount::findOne(['employee_id'=>$this->id]);
        $employee = Employee::findOne($this->id);
        if(empty($model) && $employee){
            $model = new EmployeeAccount();
            $model->employee_id = $this->id;
        }elseif(empty($model)){
            return ['status'=>false,'msg'=>'员工不存在'];
        }
        $model->qq = $this->qq;
        $model->email = $this->email;
        $model->tel = $this->phone;
        $transaction = Yii::$app->db->beginTransaction();
        try{
            if(!$model->save()){
                throw new Exception(current($model->getFirstErrors()));
            }
            if($employee->status != 0){//待入职状态 不同步权限系统
                $res = EmployeeLogic::instance()->editQxEmp($employee);
                if(!$res['status']){
                    throw new Exception($res['msg']);
                }
            }
            PeopleLogic::instance()->addLog(0,$model->employee_id,'编辑员工帐号信息',ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            $transaction->commit();
            return ['status'=>true];
        }catch(Exception $e){
            $transaction->rollBack();
            return ['status'=>false,'msg'=>$e->getMessage()];
        }
    }
    
    /**
     * 获得帐号
     * @param int $id 员工id
     * @return array
     */
    public function getAccount($id)
    {
        $model = EmployeeAccount::findOne(['employee_id'=>$id]);
        if(empty($model) && Employee::findOne($id)){
            return ['status'=>true,'data'=>[]];
        }elseif(empty($model)){
            return ['status'=>false,'msg'=>'员工不存在'];
        }
        $res = [
            'qq' => $model->qq,
            'email' => $model->email,
            'phone' => $model->tel
        ];
        return ['status'=>true,'data'=>$res];
    }
    

    /**
     * @param $id
     * @return array
     */
    public function getPersonAccount($id)
    {
        $model = EmployeeAccount::findOne(['employee_id' => $id]);
        if (empty($model)) {
            return [];
        }
        $res = [
            'qq' => $model->qq,
            'email' => $model->email,
            'phone' => $model->tel
        ];
        
        return $res;
    }
    
    
    /**
     * 获取银行卡
     * @param int $id
     * @return array
     */
    public function getBankCards($id)
    {
        $emp = Employee::findOne($id);
        if($emp){
            $data = $this->getBandCardsEmployee($emp);
            return ['status'=>true,'data'=>$data];
        }else{
            return ['status'=>false,'msg'=>'员工不存在'];
        }
    }
    
    /**
     * 银行账号
     *
     * @param $employee
     *
     * @return array
     */
    public function getBandCardsEmployee($employee)
    {
        $data = [];
        if($cards = $employee->bankCard){
            foreach($cards as $v){
                $data[] = [
                    'id'=>$v->id,
                    'bank_name' => $v->bank_name,
                    //'bank_name_des' => $v->bank_name_des,
                    'bank_card_id' => $v->bank_card_id,
                    'is_salary' => $v->is_salary,
                ];
            }
        }
        return $data;
    }

    /**
     *
     * 删除银行卡
     * @param $user
     * @return array
     */
    public function delBankCard($user)
    {
        $emp = Employee::findOne($this->id);
        $model = PersonBankInfo::findOne(['id'=>$this->bk_id,'person_id'=>$emp->person_id]);
        if(empty($model)){
            return ['status'=>false,'msg'=>'银行卡不属于该员工'];
        }
        if($model->delete()){
            PeopleLogic::instance()->addLog(0,$this->id,'删除员工银行卡信息',ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }else{
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
        }
        
    }
    
    /**
     * 编辑银行卡
     * @param $user
     * @return array
     */
    public function saveBankCard($user)
    {
        if($this->bk_id){//修改银行卡
            $model = PersonBankInfo::findOne($this->bk_id);
            $content = '修改员工银行卡信息';
        }else{//添加
            $model = new PersonBankInfo();
            $model->person_id = Employee::findOne($this->id)->person_id;
            $content = '添加员工银行卡信息';
        }
        $model->bank_card_id = $this->card_id;
        $model->bank_name = $this->bank_name;
        //$model->bank_name_des = $this->bank_des;
        $model->is_salary = $this->is_salary;
        if($model->save()){
            PeopleLogic::instance()->addLog(0,$this->id,$content,ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }else{
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
        }
    }
    /**
     * 修改劳动合同
     * @param array $user
     * @return array
     */
    public function editService($user)
    {
        $model = Employee::findOne($this->id);
        $model->service_id = $this->org_id;
        $model->service = Org::findOne($this->org_id)->org_name;
        if($model->save()){
            PeopleLogic::instance()->addLog(0,$this->id,'修改劳动合同',ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }
        return ['status'=>false, 'msg'=>current($model->getFirstErrors())];
    }
    /**
     * 获得劳动合同
     * @param int $id
     * @return array
     */
    public function getService($id)
    {
        $model = Employee::findOne($id);
        if(empty($model)){
            return ['status'=>false, 'msg'=>'员工不存在'];
        }
        $data = $this->getServiceEmployee($model);
        return ['status'=>true,'data'=>$data];
    }
    
    /**
     * 劳动关系
     *
     * @param $employee
     *
     * @return array
     */
    public function getServiceEmployee($employee)
    {
        $data = [
            'emp_id' => $employee->id,
            'org_id' => $employee->service_id,
            'org' => $employee->service,
        ];
        return $data;
    }
}