<?php
namespace app\modules\oa_v1\models;


use app\models\TalentDemand;
use app\modules\oa_v1\logic\OrgLogic;
use app\models\Job;
use app\models\Educational;
use app\modules\oa_v1\logic\TalentLogic;
use yii\helpers\ArrayHelper;
use yii\data\Pagination;
use app\modules\oa_v1\logic\BackLogic;

class TalentDemandForm extends BaseForm
{
    const SCENARIO_EDIT_DEMAND = 'edit_demand';//编辑招聘需求
    const SCENARIO_DEMAND_START = 'demand_start';//未开始
    const SCENARIO_DEMAND_ING = 'demand_ing';//进行中
    const SCENARIO_DEMAND_END = 'demand_end';//已结束
    
    public $id;
    public $org_id;
    public $profession;
    public $number;
    public $sex;
    public $edu;
    public $work_time;
    public $des;
    public $status;
    
    protected $sex_arr = [0=>'不限', 1=>'女', 2=>'男'];
    protected $status_arr = [0=>'未招聘', 1=>'招聘中', 2=>'已招聘',  3=>'已终止'];
    
    public function rules()
    {
        return [
            [
                ['org_id', 'profession', 'number', 'sex', 'edu', 'work_time'],
                'required',
                'on' => [self::SCENARIO_EDIT_DEMAND],
                'message' => '{attribute}不能为空',
            ],
            [
                ['id','status'],
                'required',
                'on' => [self::SCENARIO_DEMAND_START,self::SCENARIO_DEMAND_ING],
                'message' => '{attribute}不能为空',
            ],
            ['org_id', 'exist', 'targetClass' => '\app\models\Org', 'message' => '组织不存在！'],
            ['profession', 'exist', 'targetClass'=> '\app\models\Job', 'targetAttribute' => 'id', 'message' => '职位不存在！'],
            ['number', 'integer', 'message' => '人数不正确！'],
            ['sex', 'in', 'range' => array_keys($this->sex_arr), 'message' => '性别不正确！'],
            ['edu','exist', 'targetClass'=> '\app\models\Educational', 'targetAttribute' => 'id', 'message' => '学历不存在！'],
            ['work_time', 'string', 'message' => '工作年限不正确！'],
            ['des','string'],
            ['id', 'exist', 'targetClass' => '\app\models\TalentDemand', 'message' => '招聘需求不存在！'],
            ['status', 'in', 'range' => [0, 1], 'message' => '状态不正确'],
        ];
    }
    
    public function scenarios()
    {
        return [
            self::SCENARIO_EDIT_DEMAND => ['id','org_id','profession','number','sex','edu','work_time','des'],
            self::SCENARIO_DEMAND_START => ['id','status'],
            self::SCENARIO_DEMAND_ING => ['id','status'],
        ];
    }
   /**
    * 编辑招聘需求
    * @param array $user
    */
    public function editDemand($user)
    {
        $model = TalentDemand::findOne($this->id);
        $content = '修改招聘需求';
        if(empty($model)){
            $model = new TalentDemand();
            $model->owner = $user['person_id'];
            $content = '添加招聘需求';
        }
        $model->org_id = $this->org_id;
        $model->org_name = OrgLogic::instance()->getOrgName($this->org_id);
        $model->profession_id = $this->profession;
        $model->profession = Job::findOne($this->profession)->name;
        $model->number = $this->number;
        $model->sex = $this->sex;
        $model->edu_id = $this->edu;
        $model->edu = Educational::findOne($this->edu)->educational;
        $model->work_time = $this->work_time;
        $model->des = serialize($this->des);
        if($model->save()){
            TalentLogic::instance()->addLog(0, $content, ArrayHelper::toArray($model), $user['person_name'], $user['person_id']);
            return ['status' => true];
        }
        return ['status' => false, 'msg' => current($model->getFirstErrors())];
    }
    
    public function operate($user)
    {
        if ($this->status == 1){
            return $this->pass($user);
        }elseif ($this->status == 0){
            return $this->fail($user);
        }else{
            return ['status' => false, 'msg' => 'error'];
        }
    }
    
    public function checkScenario()
    {
        $model = TalentDemand::findOne($this->id);
        if(empty($model)){
	        $this->addError('','error');
	        return false;
	    }
	    switch($this->getScenario()){
	        case self::SCENARIO_DEMAND_START:
	            if($model->status > 0){
	                $this->addError('SCENARIO','招聘已开始');
	                return false;
	            }
	            break;
	        case self::SCENARIO_DEMAND_ING:
	            if($model->status != 1){
	                $this->addError('SCENARIO','状态不正确');
	                return false;
	            }
	            break;
            default:
                return false;
	    }
	    return true;
    }
    
    /**
     * 通过
     * @param array $user 登入的用户信息
     * @return array
     */
    protected function pass($user)
    {
        $model = TalentDemand::findOne($this->id);
        if(empty($model)){
            return ['status'=>false,'msg'=>'招聘需求不存在'];
        }
        switch($this->getScenario()){
            case self::SCENARIO_DEMAND_START://未开始
                $content = '招聘开始';
                $model->status = 1;
                break;
            case self::SCENARIO_DEMAND_ING://招聘中
                $content = '已招聘';
                $model->status = 2;
                break;
            default:
                return ['status'=>false,'msg'=>'场景错误'];
        }
        if($model->save()){
            TalentLogic::instance()->addLog(0,$content,ArrayHelper::toArray($model),$user['person_name'],$user['person_id']);
            return ['status'=>true];
        }else{
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
        }
    }
    
    /**
     * 不通过(取消招聘)
     * @param array $user 登入的用户信息
     * @return array
     */
    protected function fail($user)
    {
        $model = TalentDemand::findOne($this->id);
        if(empty($model)){
            return ['status'=>false,'msg'=>'招聘需求不存在'];
        }
        switch($this->getScenario()){
            case self::SCENARIO_DEMAND_START://未开始
                $content = '取消招聘--未开始';
                $model->status = 3;
                break;
            case self::SCENARIO_DEMAND_ING://招聘中
                $content = '取消招聘--进行中';
                $model->status = 3;
                break;
            default:
                return ['status'=>false,'msg'=>'场景错误'];
        }
        if($model->save()){
            TalentLogic::instance()->addLog(0,$content,ArrayHelper::toArray($model),$user['person_name'],$user['person_id']);
            return ['status'=>true];
        }else{
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
        }
    }
    
    /**
     * 获得详情
     * @param int $id
     * @return array
     */
    public function getInfo($id)
    {
        $model = TalentDemand::findOne($id);
        if(empty($model)){
            return ['status' => false, 'msg' => '招聘需求不存在'];
        }
        $data = [
            'org' => $model->org_name,
            'org_id' => $model->org_id,
            'profession_id' => $model->profession_id,
            'profession' => $model->profession,
            'number' => $model->number,
            'sex_name' => $this->sex_arr[$model->sex],
            'sex' => $model->sex,
            'edu_id' => $model->edu_id,
            'edu' => $model->edu,
            'work_time' => $model->work_time,
            'des' => unserialize($model->des),
        ];
        return ['status'=>true, 'data'=>$data];
    }
    
    /**
     * 获得列表
     * @param array $params
     * @return array
     */
    public function getList($params, $user, $role_name)
    {
        $keywords = ArrayHelper::getValue($params,'keywords',null);
        $start_time = ArrayHelper::getValue($params,'start_time',null);
        $end_time = ArrayHelper::getValue($params,'end_time',null);
        $page = ArrayHelper::getValue($params,'page',1);
        $page_size = ArrayHelper::getValue($params,'page_size',10);
        $sort = ArrayHelper::getValue($params,'sort',0);
        $status = ArrayHelper::getValue($params,'status',[]);
          
        $query = TalentDemand::find();
        //关键词
        if($keywords){
            $keywords = mb_convert_encoding($keywords,'UTF-8','auto');
            $query->andWhere("instr(CONCAT(profession,org_name),'{$keywords}') > 0 ");
        }
        //开始时间
        if($start_time){
            $start_time = strtotime($start_time);
            $query->andWhere(['>=', 'created_at', $start_time]);
        }
        //结束时间
        if($end_time){
            $end_time = strtotime($end_time.' 23:59:59');
            $query->andWhere(['<=', 'created_at', $end_time]);
        }
        //状态
        if($status){
            $arr_status = [];
            foreach($status as $v){
                switch($v){
                    case 0://未开始
                        array_push($arr_status ,0);
                        break;
                    case 1://进行中
                        array_push($arr_status ,1);
                        break;
                    case 2://已招聘
                        array_push($arr_status ,2);
                        break;
                    case 3://取消
                        array_push($arr_status ,3);
                        break;
                    default:
                        break;
                }
            }
            if(count($arr_status) == 1){
                $query -> andWhere(['status'=>$arr_status[0]]);
            }elseif(count($arr_status) > 1){
                $query -> andWhere(['in','status',$arr_status]);
            }
        }
        
        //除招聘经理外 只能看自己添加的~
        if(!TalentLogic::instance()->isManager($role_name)){
            $query->andWhere(['owner' => $user['person_id']]);
        }
        
        //排序
        switch($sort){
            default:
                $order_by = 'created_at desc';
                break;
        }
         
        //分页
        $pagination = new Pagination([
            'defaultPageSize' => $page_size,
            'totalCount' => $query->count(),
        ]);
         
        $res = $query->orderBy($order_by)
        ->offset($pagination->offset)
        ->limit($pagination->limit)
        ->all();
        
        $data = [];
        foreach ($res as $k => $v){
            $data[] = [
                'id' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                'demand_id' => $v->id,
                'org' => $v->org_name,
                'org_id' => $v->org_id,
                'org_ids' => OrgLogic::instance()->getOrgIdByChild($v->org_id),
                'profession' => $v->profession,
                'profession_id' => $v->profession_id,
                'number' => $v->number,
                'sex_name' => $this->sex_arr[$v->sex],
                'sex' => $v->sex,
                'edu' => $v->edu,
                'edu_id' => $v->edu_id,
                'work_time' => $v->work_time,
                'status' => $this->status_arr[$v->status],
                'status_value' => $v->status,
                'des' => unserialize($v->des),
            ];
        }
         
        return [
            'res' => $data,
            'page' => BackLogic::instance()->pageFix($pagination)
        ];
    }
}