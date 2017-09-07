<?php
namespace app\modules\oa_v1\models;

use app\logic\server\QuanXianServer;
use app\models\Apply;
use app\models\ApprovalConfig;
use app\models\Employee;
use app\models\Job;
use app\models\PeoplePic;
use app\modules\oa_v1\logic\ApprovalConfigLogic;
use app\modules\oa_v1\logic\OrgLogic;
use app\modules\oa_v1\logic\BackLogic;
use app\modules\oa_v1\logic\ProjectLogic;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use app\models\Person;
use app\models\Org;

class ApprovalConfigForm extends BaseForm
{
    const SCENARIO_EDIT = 'edit';//编辑
    const SCENARIO_APPROVAL_EDIT = 'approval_edit';//编辑审批人
    const SCENARIO_COPY_EDIT = 'copy_edit';//编辑抄送人
    const SCENARIO_APPROVAL_COPY = 'approval_copy';//复制审批人
    const SCENARIO_APPROVAL_DEL = 'approval_del';//删除审批配置


    public $id;
    public $org_id;
    public $apply_type;
    public $type;
    public $config;
    public $copy_config;
    public $distinct;
    public $copy_rule;

    public $org_pid = 1;//组织架构parent_id
    public $approval_arr = [
        1,//员工
        2,//部门负责人
        3,//职位
    ];


    protected $roles_arr = [
        'caiwujingli' => [1,2,3,4],
        'xingzhengjingli' => [5,6,7,8,9],
        'zhaopinjingli' => [10,11,12],
        'duodianshenpi' => [14],
        'guanliyuan' => [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16],
    ];

    public function rules()
    {
        return [
            [
                ['org_id','apply_type'],
                'required',
                'on' => [self::SCENARIO_EDIT],
                'message' => '{attribute}不能为空'
            ],
            [
                ['id','type','distinct','copy_rule'],
                'required',
                'on' => [self::SCENARIO_APPROVAL_EDIT],
                'message' => '{attribute}不能为空'
            ],
            [
                ['id','config'],
                'required',
                'on' => [self::SCENARIO_COPY_EDIT],
                'message' => '{attribute}不能为空'
            ],
            [
                ['id','org_id','apply_type'],
                'required',
                'on' => [self::SCENARIO_APPROVAL_COPY],
                'message' => '{attribute}不能为空'
            ],
            ['id','exist','targetClass'=>'\app\models\ApprovalConfig','message'=>'配置不存在！'],
            //['org_id','exist','targetClass'=>'\app\models\Org','targetAttribute'=>['org_id'=>'org_id','org_pid'=>'pid'],'message'=>'适用组织不正确！'],
            ['org_id','exist','targetClass'=>'\app\models\Org', 'message'=>'适用组织不正确！'],
            ['org_id','checkOrg'],
            ['apply_type','in','range'=>array_keys(Apply::TYPE_ARRAY),'message'=>'审批类型不正确！'],
            ['type','in','range'=>[0,1],'message'=>'条件不正确！'],
            ['config','checkConfigApproval','on'=>[self::SCENARIO_APPROVAL_EDIT]],
            ['distinct','in','range'=>[0,1],'message'=>'是否允许审批人重复不正确！'],
            ['copy_rule','in','range'=>[0,1],'message'=>'抄送规则不正确！']
        ];
    }

    public function checkConfigApproval($attribute)
    {
        if (!$this -> hasErrors()) {
            $data = json_decode($this->$attribute,1);
            if($data){
                if($this->apply_type == 16){
                    $project_arr = ProjectLogic::instance()->getProjects();
                    $project_arr = ArrayHelper::getColumn($project_arr,'id');
                    foreach($data as $k=>$v){
                        if(!in_array($k,$project_arr)){
                            $this->addError($attribute, "条件不正确！");
                            return false;
                        }
                    }
                }else{
                    foreach($data as $k=>$v){
                        if(!is_int($k)){
                            $this->addError($attribute, "条件不正确！");
                            return fasle;
                        }
                    }
                }
                foreach($data as $k => $v){
                    foreach($v as $vv) {
                        if (in_array($vv['type'], $this->approval_arr)) {
                            switch ($vv['type']) {
                                case 1://人
                                    $person = Person::findOne($vv['value']);
                                    if (empty($person)) {
                                        $this->addError($attribute, "审批人不正确~！");
                                        return false;
                                    }
                                    unset($person);
                                    break;
                                case 2://负责人
                                    if (!is_int($vv['value'])) {
                                        $this->addError($attribute, "审批级别不正确！");
                                        return false;
                                    }
                                    break;
                                case 3://职位
                                    $job = Job::findOne($vv['value']);
                                    if (empty($job)) {
                                        $this->addError($attribute, "审批职位不正确");
                                        return false;
                                    }
                                    unset($job);
                                    break;
                                default:
                                    $this->addError($attribute, "审批人类型不正确!！");
                                    return false;
                            }
                        } else {
                            $this->addError($attribute, "审批人不正确！~");
                        }
                    }
                }
            }else{
                $this->addError($attribute, "配置不正确！");
            }
        }
    }

    public function checkOrg($attribute)
    {
        if(!$this->hasErrors()){
            if($this->getScenario() == self::SCENARIO_APPROVAL_COPY || !$this->id){
                $config = ApprovalConfig::findOne(['apply_type'=>$this->apply_type,'org_id'=>$this->org_id]);
                $config && $this->addError($this->$attribute,'配置已存在！');
            }
        }
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_EDIT => ['id','org_id','apply_type'],
            self::SCENARIO_APPROVAL_EDIT => ['id','type','config','copy_config','distinct','copy_rule'],
            self::SCENARIO_COPY_EDIT => ['id','config'],
            self::SCENARIO_APPROVAL_COPY => ['id','org_id','apply_type'],
            self::SCENARIO_APPROVAL_DEL => ['id'],
        ];
    }

    /**
     * 编辑时判断权限
     * @param string $role_name 角色别名
     * @return boolean
     */
    public function checkApplyType($role_name)
    {
        if(isset($this->roles_arr[$role_name]) && in_array($this->apply_type, $this->roles_arr[$role_name])){
            return true;
        }
        $this->addError('','你没有操作此审批的权限！');
        return false;
    }

    /**
     * 编辑流程
     * @param array $user
     * @return array
     */
    public function edit($user)
    {
        $model = ApprovalConfig::findOne($this->id);
        $title = '修改流程';
        if(empty($model)){
            $model = new ApprovalConfig();
            $title = '添加流程';
        }
        $model->apply_type = $this->apply_type;
        $model->apply_name = Apply::TYPE_ARRAY[$this->apply_type];
        $model->org_id = $this->org_id;
        $model->org_name = OrgLogic::instance()->getOrgName($this->org_id);
        if($model->save()){
            ApprovalConfigLogic::instance()->addLog($title, $model->id, $model->org_name, $model->apply_name, ArrayHelper::toArray($model), $user['person_id'], $user['person_name']);
            return ['status'=>true];
        }else{
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
        }
    }

    /**
     * 修改审批人配置
     * @param array $user
     * @return array
     */
    public function editApproval($user)
    {
        $model = ApprovalConfig::findOne($this->id);
        if(empty($model)){
            return ['status'=>false,'msg'=>'配置不存在！'];
        }
        $model->approval = $this->setConfig();
        $model->type = $this->type;
        $model->copy_person = $this->setCopyConfig($this->copy_config);
        $model->copy_person_count = count($this->copy_config);
        $model->distinct = $this->distinct;
        $model->copy_rule = $this->copy_rule;
        if($model->save()){
            ApprovalConfigLogic::instance()->addLog('编辑审批配置',$model->id,$model->org_name,$model->apply_name,ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }else{
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
        }
    }
    /**
     * 修改抄送人
     * @param array $user
     * @return array
     */
    public function editCopyPerson($user)
    {
        $model = ApprovalConfig::findOne($this->id);
        if(empty($model)){
            return ['status'=>false,'msg'=>'配置不存在！'];
        }
        $model->copy_person = $this->setCopyConfig($this->config);
        $model->copy_person_count = count($this->config);
        if($model->save()){
            ApprovalConfigLogic::instance()->addLog('编辑抄送人',$model->id,$model->org_name,$model->apply_name,ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }else{
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
        }
    }

    /**
     * 复制审批流程
     * @param $user
     * @return array
     */
    public function copyApproval($user)
    {
        $_model = ApprovalConfig::findOne($this->id);
        if(empty($_model)){
            return ['status'=>false,'msg'=>'配置不存在！'];
        }
        $model = new ApprovalConfig();
        $model->apply_type = $this->apply_type;
        $model->apply_name = Apply::TYPE_ARRAY[$this->apply_type];
        $model->org_id = $this->org_id;
        $model->org_name = OrgLogic::instance()->getOrgName($this->org_id);
        $model->type = $_model->type;
        $model->approval = $_model->approval;
        $model->copy_person = $_model->copy_person;
        $model->copy_person_count = $_model->copy_person_count;
        if($model->save()){
            ApprovalConfigLogic::instance()->addLog('复制审批流程',$model->id,$model->org_name,$model->apply_name,ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }else{
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
        }
    }

    /**
     * 删除审批流程
     * @param $user
     * @param $role_name
     * @return array
     */
    public function delApproval($user,$role_name)
    {
        $tmp = $this->roles_arr[$role_name];
        $model = ApprovalConfig::findOne($this->id);
        if(empty($model)){
            return ['status'=>false,'msg'=>'配置不存在！'];
        }
        if(!in_array($model->apply_type,$tmp)){
            return ['status'=>false,'msg'=>'你没有删除此配置权限！'];
        }
        if(!$model->delete()){
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
        }else{
            ApprovalConfigLogic::instance()->addLog('删除审批流程',$model->id,$model->org_name,$model->apply_name,ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }
    }

    /**
     * 获得配置列表
     * @param array $params
     * @param string $roleName
     * @return array
     */
    public function getList($params,$role_name='')
    {
        $keywords = trim(ArrayHelper::getValue($params,'keywords',null));
        $start_time = ArrayHelper::getValue($params,'start_time',null);
        $end_time = ArrayHelper::getValue($params,'end_time',null);
        $page = ArrayHelper::getValue($params,'page',1);
        $page_size = ArrayHelper::getValue($params,'page_size',10);
        $sort = ArrayHelper::getValue($params,'sort','');


        $query = ApprovalConfig::find();
        //关键词
        if($keywords){
            $keywords = mb_convert_encoding($keywords,'UTF-8','auto');
            $query->andWhere("instr(CONCAT(apply_name,org_name),'{$keywords}') > 0 ");
        }
        //开始时间
        if($start_time){
            $start_time = strtotime($start_time);
            $query->andWhere(['>=', 'updated_at', $start_time]);
        }
        //结束时间
        if($end_time){
            $end_time = strtotime($end_time.' 23:59:59');
            $query->andWhere(['<=', 'updated_at', $end_time]);
        }
        //权限
        if($role_name && isset($this->roles_arr[$role_name])){
            $query->andWhere(['in','apply_type',$this->roles_arr[$role_name]]);
        }else{
            $query->andWhere('1 <> 1');
        }
        //排序
        switch($sort){
            case 'asc':
                $order_by = 'updated_at asc';
                break;
            case 'desc':
                $order_by = 'updated_at desc';
                break;
            default:
                $order_by = 'updated_at desc';
                break;
        }

        //分页
        $pagination = new Pagination([
            'defaultPageSize' => $page_size,
            'totalCount' => $query->count(),
        ]);
        /**
         * @var $res ApprovalConfig
         */
        $res = $query->orderBy($order_by)
        ->offset($pagination->offset)
        ->limit($pagination->limit)
        ->all();

        $data = [];
        foreach ($res as $k => $v){
            $data[] = [
                'id' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                'config_id' => $v->id,
                'apply_type' => $v->apply_type,
                'apply_name' => $v->apply_name,
                'org_id' => $v->org_id,
                'org_name' => $v->org_name,
                'org_ids' => OrgLogic::instance()->getOrgIdByChild($v->org_id),
                'set_approval' => $v->approval? 1 : 0,//审批人是否设置
                //'copy_person' => $this->getCopyConfig($v->copy_person),
                'copy_person_count' => $v->copy_person_count,
                'time' => date('Y-m-d H:i:s',$v->updated_at),
            ];
        }

        return [
            'res' => $data,
            'page' => BackLogic::instance()->pageFix($pagination)
        ];
    }

    /**
     * 获得详情
     * @param int $id 配置id
     */
    public function getInfo($id)
    {
        $model = ApprovalConfig::findOne($id);
        if(empty($model)){
            return ['status' => false, 'msg'=>'配置不存在！'];
        }
        $data = [
            'apply_type' => $model->apply_type,
            'apply_name' => $model->apply_name,
            'org_id' => $model->org_id,
            'org_name' => $model->org_name,
            'type' => $model->type,
            'approval' => $this->getConfig($model->approval),
            'copy_person' => $this->getCopyConfig($model->copy_person),
            'copy_person_count' => $model->copy_person_count,
            'distinct' => $model->distinct,
            'copy_rule' => $model->copy_rule,
            'time' => date('Y-m-d H:i:s',$model->updated_at),
        ];
        return ['status' => true, 'data'=>$data];
    }
    /**
     * 获得审批配置
     * @param int $org_id
     * @param int $apply_type
     * @return array
     */
    public function getApprovalConfig($user,$apply_type)
    {
    	$org_id = $user->org_id;
        /**
         * @var $model ApprovalConfig
         */
        $model = null;
        while(!$model){
        	$model = ApprovalConfig::find()->where(['org_id'=>$org_id,'apply_type'=>$apply_type])->orderBy('updated_at desc')->one();
        	if(!$model && $org_id >= 1){
        		$org = Org::findOne($org_id);
        		$org_id = $org->pid;
        		continue;
        	}
        	break;
        }
        $data = [];
        if($model){
            $data = [
                'approval' => [],
                'copy_person' => $this->getCopyConfig($model->copy_person),
                'distinct' => $model->distinct,
                'copy_rule' => $model->copy_rule,
            ];
            $approval = $this->getConfig($model->approval,$org_id);
            foreach ($approval as $k => &$v) {
                $person_tmp = [$user->person_id];
                foreach ($v['approval'] as $kk => &$vv) {
                    if(in_array($vv['type'], [1, 2])){
                        if(in_array($vv['id'], $person_tmp)) {
                            unset($vv);
                        }elseif( $model->distinct ){//是否去重
                            $person_tmp[] = $vv['id'];
                        }
                    }elseif($vv['type'] == 3){
                        foreach($vv['person'] as $kkk => &$vvv){
                            if(in_array($vvv['id'], $person_tmp)){
                                unset($vvv);
                            }elseif( $model->distinct ){//是否去重
                                $person_tmp[] = $vvv['id'];
                            }
                        }
                        if(count($vv['person']) <= 0){
                            unset($vv);
                        }
                    }
                }
            }
            $data['approval'] = $approval;
        }
        return $data;
    }

    /**
     * 格式化审批人配置入库
     */
    protected function setConfig()
    {
        $data = json_decode($this->config,1);
        $data && ksort($data);
        return $data ? json_encode($data) : '';
    }
    /**
     * 格式化审批人配置出库
     * @param string $config
     * @param int   org_id
     */
    protected function getConfig($config,$org_id = 0)
    {
        $data = json_decode($config,true);
        $data && ksort($data);
        $res = [];
        if($data){
            foreach($data as $k => $v){
                $tmp = [];
                foreach($v as $vv){
                    switch($vv['type']){
                        case 1://人
                            $tmp[] = $this->getPersonById($vv['value']);
                            break;
                        case 2://负责人
                            $tmp_person = $this->getPersonByLevel($org_id,$vv['value']);
                            $tmp_person && $tmp[] = $tmp_person;
                            break;
                        case 3://职位
                            $tmp_job = $this->getJobById($vv['value'],$org_id);
                            $tmp_job && $tmp[] = $tmp_job;
                            break;
                    }

                }
                $res[] = [
                    'key' => $k,
                    'approval' => $tmp,
                ];
            }
        }
        return $res;
    }

    /**
     * 获得审批人信息
     * @param $person_id
     * @param int $type 1:人 2:负责人
     * @return array
     */
    protected function getPersonById($person_id,$type=1)
    {
        $person = (new \yii\db\Query())->select('*')->from(Person::tableName().' p')->where(['p.person_id'=>$person_id])->leftJoin(Employee::tableName().' e','e.person_id=p.person_id')->leftJoin(PeoplePic::tableName().' pic','pic.employee_id=e.id')->one();
        $tmp = [
            'type' => $type,
            'id' => $person['person_id'],
            'label' => $person['person_name'],
            'org' => $person['org_full_name'],
            'pic' => $person['pic']?:'',
            'default' => 1,//默认，前端不能删除
        ];
        return $tmp;
    }

    /**
     * 获得负责人信息
     * @param $org_id
     * @param $level 几级负责人
     * @return array
     */
    protected function getPersonByLevel($org_id,$level)
    {
        if($org_id <= 0) {
            $tmp = [
                'type' => 2,
                'id' => $level
            ];
        }else {//查询负责人
            $tmp = [];
            $i = 1;
            $model = Org::findOne($org_id);
            while ($model) {
                if ($i < $level || $model->manager <= 0) {
                    $model = $model->parent;
                    $i++;
                    continue;
                } else {
                    $tmp = $this->getPersonById($model->manager, 2);
                    $tmp['level'] = $level;
                    break;
                }
            }
        }
        return $tmp;
    }

    /**
     * 获得职位
     * @param $id
     * @return array
     */
    protected function getJobById($id,$org_id)
    {
        if($org_id <= 0 ) {
            $job = Job::findOne($id);
            $tmp = [
                'type' => 3,
                'id' => $job->id,
                'name' => $job->name,
            ];
        }else{
            $tmp = [];
            $job = Job::findOne($id);
            $company_id = QuanXianServer::instance()->getCompanyId($org_id);
            $persons = (new \yii\db\Query())
                ->select('p.*,pic.pic')
                ->from(Person::tableName().' p')
                ->leftJoin(Employee::tableName().' e','e.person_id=p.person_id')
                ->leftJoin(PeoplePic::tableName().' pic','pic.employee_id=e.id')
                ->where(['p.company_id'=>$company_id,'e.profession'=>$job->id])
                ->all();
            if($persons) {
                $tmp = [
                    'type' => 3,
                    'id' => $job->id,
                    'name' => $job->name,
                ];
                foreach ($persons as $person) {
                    $tmp['person'][] = [
                        'id' => $person['person_id'],
                        'label' => $person['person_name'],
                        'org' => $person['org_full_name'],
                        'pic' => $person['pic'] ?: '',
                    ];
                }
            }
        }
        return $tmp;
    }


    /**
     * 格式化抄送人配置入库
     */
    protected function setCopyConfig(&$config)
    {
        $data = '';
        if($config) {
            foreach ($config as $k => $v) {
                if ($v <= 0) {
                    unset($config[$k]);
                }
            }
            $data = implode(',', $config);
        }
        return $data;
    }
    /**
     * 格式化抄送人配置出库
     * @param string $config
     */
    protected function getCopyConfig($config)
    {
        $data = explode(',', $config);
        $res = [];
        if($data){
            foreach($data as $v){
                if($v <= 0){
                    continue;
                }
                //$person = Person::findOne($v);
                $person = (new \yii\db\Query())->select('*')->from(Person::tableName().' p')->where(['p.person_id'=>$v])->leftJoin(Employee::tableName().' e','e.person_id=p.person_id')->leftJoin(PeoplePic::tableName().' pic','pic.employee_id=e.id')->one();
                $res[] = [
                    'id' => $person['person_id'],
                    'label' => $person['person_name'],
                    'org' => $person['org_full_name'],
                    'pic' => $person['pic'] ?:'',
                    'default' => 1,//默认，前端不能删除
                ];
            }
        }
        return $res;
    }
    /**
     * 获得可配置的审批类型
     * @param unknown $role_name
     */
    public function getApplyType($role_name)
    {
        $data = [];
        $tmp = $this->roles_arr[$role_name];
        if($tmp){
            foreach($tmp as $v){
                $data[] = [
                    'label'=>'申请'.Apply::TYPE_ARRAY[$v],
                    'value'=>$v,
                ];
            }
        }
        return $data;
    }

}