<?php
namespace app\modules\oa_v1\models;

use app\models\Talent;
use app\modules\oa_v1\logic\TalentLogic;
use yii\helpers\ArrayHelper;
use yii\data\Pagination;
use app\modules\oa_v1\logic\BackLogic;

/**
 * 人才表单
 * @author yjr
 *
 */
class TalentForm extends BaseForm
{
	const SCENARIO_ADD_ZHAOPIN = 'add_zhaopin';//添加招牌
	const SCENARIO_COMMUNION = 'communion';//沟通
	const SCENARIO_TEST = 'test';//考试
	const SCENARIO_FACE = 'face';//面试
	
	public $name;
	public $phone;
	public $job;
	public $sex;
	public $age;
	public $educational;
	public $work_time;
	public $current_location;
	public $id;
	public $status;
	
	public $status_arr = [
	    '1' => '待沟通',
	    '2' => '待考试',
	    '3' => '待面试',
	    '4' => '不合适',
	    '5' => '录用',
	];
	
	public function rules()
	{
		return [
		    [
		        ['name','phone','job','sex','age','educational','work_time','current_location'],
		        'required',
		        'message'=>'{attribute}不能为空',
		    ],
		    [
		        ['id','status'],
		        'required',
		        'on'=>[self::SCENARIO_ADD_ZHAOPIN ,self::SCENARIO_COMMUNION ,self::SCENARIO_TEST,self::SCENARIO_FACE ],
		        'message'=>'{attribute}不能为空',
		    ],
		    ['id','exist','targetClass'=>'\app\models\Talent','targetAttribute'=>'id','message'=>'人不存在！'],
		    ['status','in', 'range' => [0, 1],'message'=>'操作错误！'],//0：不通过 1：通过
		    ['name','string','max'=>20,'message'=>'姓名错误！'],
		    ['phone','match','pattern'=>'/^1\d{10}$/','message'=>'手机号不正确!'],
		    ['job','exist','targetClass'=>'\app\models\Job','targetAttribute'=>'id','message'=>'职位不存在！'],
		    ['sex','in', 'range' => [1, 2],'message'=>'性别错误！'],//1：女  2：男
		    ['age','compare', 'compareValue' => 80, 'operator' => '<=','message'=>'年龄不得高于80岁！'],
		    ['educational','exist','targetClass'=>'\app\models\Educational','targetAttribute'=>'id','message'=>'学历不正确！'],
		    ['work_time','integer','message'=>'工作年限不正确！'],
		    ['current_location','exist','targetClass'=>'\app\models\Region','targetAttribute'=>'id','message'=>'地区不正确！']
		    
		];
	}
	
	public function scenarios()
	{
	    return [
	        self::SCENARIO_ADD_ZHAOPIN => ['name','phone','job','sex','age','educational','work_time','current_location'],
	        self::SCENARIO_COMMUNION =>['id','status'],
	        self::SCENARIO_TEST =>['id','status'],
	        self::SCENARIO_FACE => ['id','status'],
	    ];
	}
	/**
	 * 新增招聘
	 * @param array $user 登入用户的信息
	 * @return array
	 */
	public function addTalent($user)
	{
	    $model = new Talent();
	    $model->name = $this->name;
	    $model->phone = $this->phone;
	    $model->job = $this->job;
	    $model->sex = $this->sex;
	    $model->age = $this->age;
	    $model->educational = $this->educational;
	    $model->work_time = $this->work_time;
	    $model->current_location = $this->current_location;
	    $model->created_at = time();
	    if($model->save()){
	        TalentLogic::instance()->addLog($model->id,'新增招聘',ArrayHelper::toArray($model),$user['person_name'],$user['person_id']);
	        return ['status'=>true];
	    }else{
	        return ['status'=>false,'msg'=>current($model->getFirstErrors())];
	    }
	}
	
	/**
	 * 审批操作
	 * @param array $user
	 */
	public function operate($user)
	{
	    if($this->status == 1){
	        return $this->pass($user);
	    }elseif($this->status == 0){
	        return $this->fail($user);
	    }else{
	        return ['status'=>false];
	    }
	}
	
	/**
	 * 审核通过
	 * @param array $user 登入的用户信息
	 * @return array
	 */
	protected function pass($user)
	{
	    $model = Talent::findOne($this->id);
	    if(empty($model)){
	        return ['status'=>false,'msg'=>'人不存在'];
	    }
	    switch($this->getScenario()){
	        case self::SCENARIO_COMMUNION://沟通
	            $content = '沟通通过';
	            $model->status_communion = $model->status_communion ?: 1;
	            $model->status = 2;
	            break;
	        case self::SCENARIO_TEST://考试
	            $content = '考试通过';
	            $model->status_test = $model->status_test ?: 1;
	            $model->status = 3;
	            break;
	        case self::SCENARIO_FACE://面试
	            $content = '面试通过';
	            $model->status_face = $model->status_face ?: 1;
	            $model->status = 5;
	            break;
	        default:
	            return ['status'=>false,'msg'=>'场景错误'];
	    }
	    if($model->save()){
	        TalentLogic::instance()->addLog($model->id,$content,ArrayHelper::toArray($model),$user['person_name'],$user['person_id']);
	        return ['status'=>true];
	    }else{
	        return ['status'=>false,'msg'=>current($model->getFirstErrors())];
	    }
	}
	
	/**
	 * 审核不通过
	 * @param array $user 登入的用户信息
	 * @return array
	 */
	protected function fail($user)
	{
	    $model = Talent::findOne($this->id);
	    if(empty($model)){
	        return ['status'=>false,'msg'=>'人不存在'];
	    }
	    switch($this->getScenario()){
	        case self::SCENARIO_COMMUNION://沟通
	            $content = '沟通不通过';
	            $model->status_communion = $model->status_communion ?: 2;
	            $model->status = 4;
	            break;
	        case self::SCENARIO_TEST://考试
	            $content = '考试不通过';
	            $model->status_test = $model->status_test ?: 2;
	            $model->status = 4;
	            break;
	        case self::SCENARIO_FACE://面试
	            $content = '面试不通过';
	            $model->status_face = $model->status_face ?: 2;
	            $model->status = 4;
	            break;
	        default:
	            return ['status'=>false,'msg'=>'场景错误'];
	    }
	    if($model->save()){
	        TalentLogic::instance()->addLog($model->id,$content,ArrayHelper::toArray($model),$user['person_name'],$user['person_id']);
	        return ['status'=>true];
	    }else{
	        return ['status'=>false,'msg'=>current($model->getFirstErrors())];
	    }
	}
	
	/**
	 * 移入人才库
	 * @param array $user 登入用户
	 */
	public function joinTalent($id,$user)
	{
	    $model = $model = Talent::findOne($id);
	    if(empty($model)){
	        return ['status'=>false,'msg'=>'人不存在'];
	    }
	    $model->talent = 1;
	    if($model->save()){
	        TalentLogic::instance()->addLog($id,'移入人才库',ArrayHelper::toArray($model),$user['person_name'],$user['person_id']);
	        return ['status'=>true];
	    }else{
	        return ['status'=>false,'msg'=>current($model->getFirstErrors())];
	    }
	}
	
	/**
	 * 获得列表
	 * @param array $params
	 * @return array
	 */
	public function getList($params)
	{
	    $keywords = ArrayHelper::getValue($params,'keywords',null);
	    $start_time = ArrayHelper::getValue($params,'start_time',null);
	    $end_time = ArrayHelper::getValue($params,'end_time',null);
	    $page = ArrayHelper::getValue($params,'page',1);
	    $page_size = ArrayHelper::getValue($params,'page_size',10);
	    $status = ArrayHelper::getValue($params, 'status',0);
	    $talent = ArrayHelper::getValue($params, 'talent',0);
	    
	    $query = Talent::find();
	    //关键词
	    if($keywords){
	        $keywords = mb_convert_encoding($keywords,'UTF-8','auto');
	        $query->andWhere(['like', 'name', $keywords]);
	    }
	    //开始时间
	    if($start_time){
	        $start_time = strtotime($start_time);
	        $query->andWhere(['>=', 'updated_at', $start_time]);
	    }
	    //结束时间
	    if($end_time){
	        $end_time = strtotime($end_time);
	        $query->andWhere(['<=', 'updated_at', $end_time]);
	    }
	    //人才库
	    if($talent){
	        $query->andWhere(['talent'=>1]);
	    }
	    //状态
	    if($status){
	    	$orwhere = '1<>1 ';
	        foreach($status as $v){
	            switch($v){
	                case 1://待沟通
	                    $orwhere .= " or status = 1";
	                    break;
	                case 2://待考试
	                    $orwhere .= " or status = 2";
	                    break;
	                case 3://带面试
	                	$orwhere .= " or status = 3";
	                    break;
	                case 4://不通过
	                	$orwhere .= " or status = 4";
	                    break;
	                case 5://录用
	                    $orwhere .= " or status = 5";
	                    break;
	                default:
	                    break;
	            }
	        }
	        $query->andWhere($orwhere);
	    }
	    
	    //分页
	    $pagination = new Pagination([
	        'defaultPageSize' => $page_size,
	        'totalCount' => $query->count(),
	    ]);
	    
	    $res = $query->orderBy("created_at desc")
	    ->offset($pagination->offset)
	    ->limit($pagination->limit)
	    ->all();
	    
	    $data = [];
	    foreach($res as $k => $v){
	        $person_type = 
	    	$data[] = [
	    	    'id' => $pagination->pageSize * $pagination->getPage() + $k + 1,
	    	    'talent_id' => $v->id,
	    	    'name' => $v->name,
	    	    'phone' => $v->phone,
	    	    'profession' => $v->profession->name,
	    	    'sex' => $v->sex == 1 ? '女' : '男',
	    	    'age' => $v->age,
	    	    'educational' => $v->edu->educational,
	    	    'work_time' => $v->work_time,
	    	    'status' => $this->status_arr[$v->status],
	    	    'status_value' => $v->status,
	    	    'person_type' => $v->person_type > 0 ? $v->type->name : '',
	    	];
	    }
	    
	    return [
	        'data' => $data,
	        'pages' => BackLogic::instance()->pageFix($pagination)
	    ];
	}
	
	/**
	 * 判断场景
	 */
	public function checkScenario()
	{
	    $model = Talent::findOne($this->id);
	    if(empty($model)){
	        $this->addError('','');
	    }
	    switch($this->getScenario()){
	        case self::SCENARIO_COMMUNION://沟通
	            if($model->status_communion > 0){
	                $this->addError('SCENARIO','已沟通过');
	                return false;   
	            }
	            break;
	        case self::SCENARIO_TEST://考试
	            if($model->status_communion == 0){
	                $this->addError('SCENARIO','还未沟通');
	                return false;
	            }elseif($model->status_communion == 2){
	                $this->addError('SCENARIO','沟通未通过，不可考试');
	                return false;
	            }elseif($model->status_test > 0){
	                $this->addError('SCENARIO','已考过试');
	                return false;
	            }
	            break;
	        case self::SCENARIO_FACE://面试
	            if($model->status_test == 0){
	                $this->addError('SCENARIO','还未考试');
	                return false;
	            }elseif($model->status_test == 2){
	                $this->addError('SCENARIO','考试未通过，不可面试');
	                return false;
	            }elseif($model->status_face > 0){
	                $this->addError('SCENARIO','已面试过');
	                return false;
	            }
	            break;
	        default:
	            return false;
	    }
	    return true;
	}
}