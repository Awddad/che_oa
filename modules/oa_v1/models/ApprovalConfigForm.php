<?php
namespace app\modules\oa_v1\models;

use app\models\ApprovalConfig;
use app\modules\oa_v1\logic\ApprovalConfigLogic;
use app\modules\oa_v1\logic\OrgLogic;
use app\modules\oa_v1\logic\BackLogic;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use app\models\Person;
use app\models\Org;

class ApprovalConfigForm extends BaseForm
{
    const SCENARIO_EDIT = 'edit';//编辑
    const SCENARIO_APPROVAL_EDIT = 'approval_edit';//编辑审批人
    const SCENARIO_COPY_EDIT = 'copy_edit';//编辑抄送人


    public $id;
    public $org_id;
    public $apply_type;
    public $type;
    public $config;

    public $org_pid = 1;//组织架构parent_id

    protected $roles_arr = [
        'caiwujingli' => [1,2,3,4,5],
        'xingzhengjingli' => [6,7,8,9],
        'zhaopinjingli' => [10,11,12],
        'duodianshenpi' => [14],
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
                ['id','type','config'],
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
            ['id','exist','targetClass'=>'\app\models\ApprovalConfig','message'=>'配置不存在！'],
            //['org_id','exist','targetClass'=>'\app\models\Org','targetAttribute'=>['org_id'=>'org_id','org_pid'=>'pid'],'message'=>'适用组织不正确！'],
            ['org_id','exist','targetClass'=>'\app\models\Org', 'message'=>'适用组织不正确！'],
            ['org_id','checkOrg'],
            ['apply_type','in','range'=>array_keys($this->typeArr),'message'=>'审批类型不正确！'],
            ['type','in','range'=>[0,1],'message'=>'条件不正确！'],
            ['config','checkConfigApproval','on'=>[self::SCENARIO_APPROVAL_EDIT]],
        ];
    }

    public function checkConfigApproval($attribute)
    {
        if (!$this -> hasErrors()) {
            $data = json_decode($this->$attribute,1);
            if($data){
                foreach($data as $k => $v){
                    if(!is_int($k)){
                        $this->addError($attribute, "条件不正确！");
                    }else{
                        foreach($v as $vv){
                            $person = Person::findOne($vv);
                            if(empty($person)){
                                $this->addError($attribute, "审批人不正确！");
                            }
                            unset($person);
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
            if(!$this->id){
                $config = ApprovalConfig::findOne(['apply_type'=>$this->apply_type,'org_id'=>$this->org_id]);
                $config && $this->addError($this->$attribute,'配置已存在！');
            }
        }
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_EDIT => ['id','org_id','apply_type'],
            self::SCENARIO_APPROVAL_EDIT => ['id','type','config'],
            self::SCENARIO_COPY_EDIT => ['id','config'],
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
        $model->apply_name = $this->typeArr[$this->apply_type];
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
        if($model->save()){
            ApprovalConfigLogic::instance()->addLog('编辑审批人',$model->id,$model->org_name,$model->apply_name,ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
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
        $model->copy_person = $this->setCopyConfig();
        $model->copy_person_count = count($this->config);
        if($model->save()){
            ApprovalConfigLogic::instance()->addLog('编辑抄送人',$model->id,$model->org_name,$model->apply_name,ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }else{
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
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
            'time' => date('Y-m-d H:i:s',$model->updated_at),
        ];
        return ['status' => true, 'data'=>$data];
    }
    /**
     * 获得审批配置
     * @param int $org_id
     * @param int $apply_type
     */
    public function getApprovalConfig($user,$apply_type)
    {
    	$org_id = $user->org_id;
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
                'approval' => $this->getConfig($model->approval),
                'copy_person' => $this->getCopyConfig($model->copy_person),
            ];
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
        return json_encode($data);
    }
    /**
     * 格式化审批人配置出库
     * @param string $config
     */
    protected function getConfig($config)
    {
        $data = json_decode($config,true);
        $data && ksort($data);
        $res = [];
        if($data){
            foreach($data as $k => $v){
                $tmp = [];
                foreach($v as $vv){
                    $person = Person::findOne($vv);
                    $tmp[] = [
                        'id' => $person['person_id'],
                        'label' => $person['person_name'],
                        'org' => $person['org_full_name'],
                        'default' => 1,//默认，前端不能删除
                    ];
                }
                $res[] = [
                    'min' => $k,
                    'approval' => $tmp,
                ];
            }
        }
        return $res;
    }

    /**
     * 格式化抄送人配置入库
     */
    protected function setCopyConfig()
    {
        foreach($this->config as $k => $v){
            if($v <= 0){
                unset($this->config[$k]);
            }
        }
        $data = implode(',', $this->config);
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
                $person = Person::findOne($v);
                $res[] = [
                    'id' => $person['person_id'],
                    'label' => $person['person_name'],
                    'org' => $person['org_full_name'],
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
                    'label'=>'申请'.$this->typeArr[$v],
                    'value'=>$v,
                ];
            }
        }
        return $data;
    }

}