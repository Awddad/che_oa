<?php
namespace app\modules\oa_v1\models;

use yii;
use app\models\Employee;
use yii\helpers\ArrayHelper;
use app\modules\oa_v1\logic\OrgLogic;
use yii\data\Pagination;
use app\modules\oa_v1\logic\BackLogic;
use app\models\EmployeeType;
use app\models\EmployeeAccount;
use app\models\Org;
use app\modules\oa_v1\logic\EmployeeLogic;
use yii\db\Exception;

class EmployeeForm extends BaseForm
{
    const SCENARIO_ADD_EMPLOYEE = 'add_employee';
    const SCENARIO_ENTRY = 'entry';
    const SCENARIO_CANCEL = 'cancel';
    
    public $employee_id;
    public $email;
    public $qq;
    public $name;
    public $phone;
    public $profession;
    public $org_id;
    public $entry_time;
    
    public function rules()
    {
        return [
            [
                ['org_id','name','phone','profession','entry_time','qq'],
                'required',
                'on' => [self::SCENARIO_ADD_EMPLOYEE],
                'message' => '{attribute}不能为空'
            ],
            [
                ['employee_id','email'],
                'required',
                'on' => [self::SCENARIO_ENTRY],
                'message' => '{attribute}不能为空'
            ],
            [
                ['employee_id'],
                'required',
                'on' => [self::SCENARIO_CANCEL],
                'message' => '{attribute}不能为空'
            ],
            //['employee_id','exist','targetClass'=>'\app\models\Employee','targetAttribute'=>'id','message'=>'员工不存在'],
            ['email','email','message'=>'邮箱错误'],
            ['qq','string','message'=>'qq不正确'],
            ['employee_id','checkEmployeeID'],
            ['phone','match','pattern'=>'/^1\d{10}$/','message'=>'手机号不正确!'],
            ['phone','unique','targetClass'=>'\app\models\Talent','targetAttribute'=>['phone'],'message'=>'招聘管理存在此人，请走招聘流程!','on'=>[self::SCENARIO_ADD_EMPLOYEE]],
            ['phone','unique','targetClass'=>'\app\models\Employee','targetAttribute'=>['phone'],'message'=>'此人已入职!','on'=>[self::SCENARIO_ADD_EMPLOYEE]],
            ['entry_time','date','format' => 'yyyy-mm-dd','message' => '入职时间不正确'],
            ['org_id','exist','targetClass'=>'\app\models\Org','targetAttribute'=>'org_id','message'=>'组织不存在'],
            ['profession','exist','targetClass'=>'\app\models\Job','targetAttribute'=>'id','message'=>'职位不存在']
        ];
    }
    
    public function checkEmployeeID($attribute)
    {
        if (!$this->hasErrors()) {
            $emp = Employee::findOne($this->$attribute);
            if(empty($emp)){
                $this->addError($attribute, "员工不存在！");
                return;
            }elseif($emp->person_id>0){
                $this->addError($attribute, "员工已入职！");
                return;
            }
        }
    }
    
    public function scenarios()
    {
        return [
            self::SCENARIO_ADD_EMPLOYEE => ['org_id','name','phone','profession','entry_time','qq'],
            self::SCENARIO_ENTRY => ['employee_id','email','qq','phone'],
            self::SCENARIO_CANCEL => ['employee_id'],
        ];
    }
    
    /**
     * 添加员工
     */
    public function addEmployee()
    {
        /**
         * @var $employeeType EmployeeType
         */
        $employeeType = EmployeeType::find()->where(['slug'=>'shiyong'])->one();
        $model = new Employee();
        $model->name = $this->name;
        $model->phone = $this->phone;
        $model->profession = $this->profession;
        $model->org_id = $this->org_id;
        $model->entry_time = $this->entry_time;
        $model->status = 0;
        $model->employee_type = $employeeType->id;
        $tran = yii::$app->db->beginTransaction();
        try{
            if($model->save()){
                $this->employee_id = $model->id;
                $this->email = '';
                $this->phone = '';
                $res = $this->saveAccount();
                if($res['status']){
                    $tran->commit();
                    return ['status'=>true];
                }else{
                    throw new Exception($res['msg']);
                }
            }else{
                throw new Exception(current($model->getFirstErrors()));
            }
        }catch (Exception $e){
            $tran->rollBack();
            return ['status'=>false,'msg'=>$e->getMessage()];
        }
    }
    
    /**
     * 入职
     * @return array
     */
    public function entry()
    {
        $transaction = Yii::$app->db->beginTransaction();
        //保存工作帐号信息
        $res= $this->saveAccount();
        if(!$res['status']){
            $transaction->rollBack();
            return $res;
        }
        //获取员工数据
        $model = Employee::findOne($this->employee_id);
        //权限系统添加用户
        $res = EmployeeLogic::instance()->addQxEmp($model);
        if($res['status']){
            //权限系统添加成功 把权限系统的id赋到员工表
            $model->person_id = $res['id'];
            $model->status = 2;
            if($model->save()){
                $transaction->commit();
                return ['status'=>true];
            }else{
                $transaction->rollBack();
                return ['status'=>false,'msg'=>current($model->getFirstErrors())];
            }
        }
        $transaction->rollBack();
        return ['status'=>false,'msg'=>$res['msg']];
    }
    /**
     * 保存工作帐号信息
     */
    protected function saveAccount()
    {
        $model = EmployeeAccount::find()->where(['employee_id'=>$this->employee_id])->one();
        if(empty($model)){
            $model = new EmployeeAccount();
            $model->employee_id = $this->employee_id;
        }
        $model->email = $this->email;
        $model->qq = $this->qq;
        $model->tel = $this->phone;
        if($model->save()){
            return ['status'=>true];
        }
        return ['status'=>false,'msg'=>current($model->getFirstErrors())];
    }
    
    /**
     * 取消入职
     * @return array
     */
    public function cancel()
    {
        //获取员工数据
        $model = Employee::findOne($this->employee_id);
        if($model->status != 0){
            return ['status'=>false, 'msg'=>'当前状态不能取消入职'];
        }
        //修改状态
        $model->status = 1;
        if($model->save()){
            return ['status'=>true];
        }
        return ['status'=>false,'msg'=>current($model->getFirstErrors())];
    }
    
    /**
     * 获得列表
     * @param array $params
     * @return array
     */
    public function getList($params)
    {
        $arr_status = [
            0=>'待入职',
            1=>'需求入职',
            2=>'已入职',
            3=>'已离职',
            4=>'再入职'
        ];
        
        $keywords = trim(ArrayHelper::getValue($params,'keywords',null));
        //$page = ArrayHelper::getValue($params,'page',1);
        $page_size = ArrayHelper::getValue($params,'page_size',10);
        $org_id = ArrayHelper::getValue($params, 'org_id',0);
         
        $query = Employee::find();
        //关键词
        if($keywords){
            $keywords = mb_convert_encoding($keywords,'UTF-8','auto');
            $query->andWhere(['like', 'name', $keywords]);
        }
        //组织架构
        if($org_id){
            $org_ids = OrgLogic::instance()->getAllChildID($org_id);
            $query->andWhere(['in','org_id',$org_ids]);
        }
        
        //分页
        $pagination = new Pagination([
            'defaultPageSize' => $page_size,
            'totalCount' => $query->count(),
        ]);
         
        $res = $query->orderBy("entry_time asc")
        ->offset($pagination->offset)
        ->limit($pagination->limit)
        ->all();
        
        $data = [];
        /**
         * @var $v Employee
         */
        foreach($res as $k => $v){
            $data[] = [
                'id' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                'empno' => $v->person_id,
                'employee_id' => $v->id,
                'name' => $v->name,
                'phone' => $v->phone,
                'profession' => empty($v->job) ? '' : $v->job->name,
                'employee_type' => $v->employee_type > 0 ? $v->employeeType->name : '',
                'entry_time' => $v->entry_time,
                'leave_time' => $v->leave_time,
                'entry' => $v->status,
                'entry_status' => $arr_status[$v->status],
                'org' => empty($org = Org::findOne($v->org_id))?'': ($org->org_short_name ?:$org->org_name),
            ];
            unset($org);
        }
         
        return [
            'res' => $data,
            'page' => BackLogic::instance()->pageFix($pagination)
        ];
    }
}