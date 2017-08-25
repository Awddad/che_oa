<?php
namespace app\modules\oa_v1\models;

use app\models\Talent;
use app\modules\oa_v1\logic\TalentLogic;
use yii\helpers\ArrayHelper;
use yii\data\Pagination;
use app\modules\oa_v1\logic\BackLogic;
use app\models\Employee;
use yii;
use app\models\EmployeeType;
use app\models\Region;
use app\modules\oa_v1\logic\RegionLogic;
use yii\validators\RequiredValidator;
use moonland\phpexcel\Excel;
use app\models\Job;
use app\models\Educational;

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
	const SCENARIO_JOIN = 'join';//加入人才库
	
	const SCENARIO_IMPORT = 'import';//导入
    
    public $file;
	
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
	public $talent_type;
	public $org_id;
	public $entry_time;
	public $reason;
	public $choice_score;
	public $answer_score;
	public $face_time;
	
	public $status_arr = [
	    '1' => '待沟通',
	    '2' => '待考试',
	    '3' => '待面试',
	    '4' => '不合适',
	    '5' => '录用',
	];
	
	//入职时需要修改的类
	protected $peopleModel = [
	    //项目经验
	    '\app\models\PeopleProjectExperience',
	    //工作经验
	    '\app\models\PeopleWorkExperience',
	    //培训经历
	    '\app\models\PeopleTrainExperience',
	    //教育经历
	    '\app\models\PeopleEduExperience',
	    //文件
	    '\app\models\PeopleFiles',
	    //能力
	    '\app\models\PeopleAbility',
	    //头像
	    '\app\models\PeoplePic',
	];
	
	public function rules()
	{
		return [
		    [
		        ['name','phone','job','sex','age','educational','work_time','current_location'],
		        'required',
		        'on'=>[self::SCENARIO_ADD_ZHAOPIN],
		        'message'=>'{attribute}不能为空',
		    ],
		    [
		        ['id','status'],
		        'required',
		        'on'=>[self::SCENARIO_COMMUNION ,self::SCENARIO_TEST,self::SCENARIO_FACE ],
		        'message'=>'{attribute}不能为空',
		    ],
		    [
		        ['id','talent_type'],
		        'required',
		        'on'=>[self::SCENARIO_JOIN],
		        'message'=>'{attribute}不能为空',
		    ],
		    [
    		    ['file'],
    		    'required',
    		    'on' => [self::SCENARIO_IMPORT],
    		    'message' => '{attribute}不能为空',
		    ],
		    ['id','exist','targetClass'=>'\app\models\Talent','targetAttribute'=>'id','message'=>'人不存在！'],
		    ['talent_type','exist','targetClass'=>'\app\models\PersonType','targetAttribute'=>'id','message'=>'类型不存在！'],
		    ['status','in', 'range' => [0, 1],'message'=>'操作错误！'],//0：不通过 1：通过
		    ['status','checkStatus','on'=>[self::SCENARIO_COMMUNION ,self::SCENARIO_TEST,self::SCENARIO_FACE ]],
		    ['name','string','max'=>20,'message'=>'姓名错误！'],
		    ['phone','match','pattern'=>'/^1\d{10}$/','message'=>'手机号不正确!'],
		    ['phone','unique','targetClass'=>'\app\models\Talent','targetAttribute'=>['phone'],'message'=>'此人已存在,不可重复添加!'],
		    ['phone','unique','targetClass'=>'\app\models\Employee','targetAttribute'=>'phone','message'=>'此人已入职!'],
		    ['job','exist','targetClass'=>'\app\models\Job','targetAttribute'=>'id','message'=>'职位不存在！'],
		    ['sex','in', 'range' => [1, 2],'message'=>'性别错误！'],//1：女  2：男
			['age','integer','message'=>'年龄不正确'],
		    ['age','compare', 'compareValue' => 80, 'operator' => '<=','message'=>'年龄不得高于80岁！'],
		    ['educational','exist','targetClass'=>'\app\models\Educational','targetAttribute'=>'id','message'=>'学历不正确！'],
		    ['work_time','integer','message'=>'工作年限不正确！'],
		    ['current_location','exist','targetClass'=>'\app\models\Region','targetAttribute'=>'id','message'=>'地区不正确！'],
		    ['entry_time','date','format' => 'yyyy-mm-dd','message' => '入职时间不正确'],
		    ['org_id','exist','targetClass'=>'\app\models\Org','targetAttribute'=>'org_id','message'=>'组织不存在！'],
		    ['file','file', 'extensions' => ['xlsx','xls'],'checkExtensionByMimeType'=>false,'message'=>'文件格式错误'],
		    ['file','checkFile'],
		    ['reason','string','max'=>20,'message'=>'不同意原因不正确！'],
			[['choice_score','answer_score'],'integer','message'=>'分数不正确'],
			[['choice_score','answer_score'],'compare', 'compareValue' => 0, 'operator' => '>=','message'=>'分数不能低于0分！'],
			['face_time','date','format' => 'yyyy-mm-dd','message' => '面试时间不正确']
		];
	}
	
	public function checkStatus($attribute)
	{
		$validator = new RequiredValidator();
		if($this->$attribute == 1) {
			switch ($this->getScenario()) {
				case self::SCENARIO_COMMUNION:
					$need_test = Talent::findOne($this->id)->need_test;
					if(!$need_test && !$validator->validate($this->face_time)){
						$this->addError('face_time','面试时间不能为空');
						return false;
					}
					break;
				case self::SCENARIO_TEST:
					if(!$validator->validate($this->choice_score)){
						$this->addError('choice_score','分数不能为空');
						return false;
					}elseif(!$validator->validate($this->answer_score)){
						$this->addError('answer_score','分数不能为空');
						return false;
					}elseif(!$validator->validate($this->face_time)){
						$this->addError('face_time','面试时间不能为空');
						return false;
					}
					break;
				case self::SCENARIO_FACE:
					if(!$validator->validate($this->org_id)){
						$this->addError('org_id','部门不能为空');
						return false;
					}elseif(!$validator->validate($this->entry_time)){
						$this->addError('entry_time','入职时间不能为空');
						return false;
					}
					break;
			}
		}else{
			if(!$validator->validate($this->reason)){
				$this->addError('reason','不同意原因不能为空！');
				return false;
			}
		}
	    return true;
	}
	
	public function checkFile($attribute)
	{

	}
	
	public function scenarios()
	{
	    return [
	        self::SCENARIO_ADD_ZHAOPIN => ['name','phone','job','sex','age','educational','work_time','current_location'],
	        self::SCENARIO_COMMUNION =>['id','status','reason','face_time'],
	        self::SCENARIO_TEST =>['id','status','reason','choice_score','answer_score','face_time'],
	        self::SCENARIO_FACE => ['id','status','org_id','entry_time','reason'],
	        self::SCENARIO_JOIN => ['id','talent_type'],
	        self::SCENARIO_IMPORT => ['file'],
	    ];
	}
	/**
	 * 新增招聘
	 * @param array $user 登入用户的信息
	 * @return array
	 */
	public function addTalent($user)
	{
		$need_test = Job::findOne($this->job)->need_exam;
	    $model = new Talent();
		$model->need_test = $need_test;
	    $model->name = $this->name;
	    $model->phone = $this->phone;
	    $model->job = $this->job;
	    $model->sex = $this->sex;
	    $model->age = $this->age;
	    $model->educational = $this->educational;
	    $model->work_time = $this->work_time;
	    $model->current_location = $this->current_location;
	    $model->created_at = time();
	    $model->owner = $user['person_id'];
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
				$need_test = $model->need_test;
	            $content = '沟通通过';
	            $model->status_communion = $model->status_communion ?: 1;
	            $model->status = $need_test ? 2 : 3;
				$model->need_test = $need_test;
				$need_test || $model->face_time = $this->face_time ?: '';
	            break;
	        case self::SCENARIO_TEST://考试
	            $content = '考试通过';
	            $model->status_test = $model->status_test ?: 1;
	            $model->status = 3;
				$model->choice_score = $this->choice_score;
				$model->answer_score = $this->answer_score;
				$model->face_time = $this->face_time;
	            break;
	        case self::SCENARIO_FACE://面试
	            return $this->employ($user);
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
	            $model->disagree_reason = $this->reason;
	            break;
	        case self::SCENARIO_TEST://考试
	            $content = '考试不通过';
	            $model->status_test = $model->status_test ?: 2;
	            $model->status = 4;
	            $model->disagree_reason = $this->reason;
	            break;
	        case self::SCENARIO_FACE://面试
	            $content = '面试不通过';
	            $model->status_face = $model->status_face ?: 2;
	            $model->status = 4;
	            $model->disagree_reason = $this->reason;
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
	public function joinTalent($user)
	{
	    $model = $model = Talent::findOne(['id'=>$this->id,'talent'=>0]);
	    if(empty($model)){
	        return ['status'=>false,'msg'=>'人已在人才库'];
	    }
	    $model->talent = 1;
	    $model->person_type = $this->talent_type;
	    if($model->save()){
	        TalentLogic::instance()->addLog($this->id,'移入人才库',ArrayHelper::toArray($model),$user['person_name'],$user['person_id']);
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
	public function getList($params,$user,$role_name)
	{
	    $keywords = trim(ArrayHelper::getValue($params,'keywords',null));
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
	        //$query->andWhere(['like', 'name', $keywords]);
	        $query->andWhere("instr(CONCAT(name,phone),'{$keywords}') > 0 ");
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
	    //人才库
	    if($talent){
	        $query->andWhere(['talent'=>1]);
	    }else{
	        $query->andWhere(['talent'=>0]);
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
	                case 3://待面试
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
	    //除招聘经理外 只能看自己添加的~
	    if(!TalentLogic::instance()->isManager($role_name)){
	        $query->andWhere(['owner'=>$user['person_id']]);
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
		if($res) {
			foreach ($res as $k => $v) {
				$data[] = [
					'id' => $pagination->pageSize * $pagination->getPage() + $k + 1,
					'talent_id' => $v->id,
					'name' => $v->name,
					'phone' => $v->phone,
					'profession' => empty($v->profession) ? '' : $v->profession->name,
					'sex' => $v->sex == 1 ? '女' : '男',
					'age' => $v->age,
					'educational' => empty($v->edu) ? '' : $v->edu->educational,
					'work_time' => $v->work_time . '年',
					'status' => $this->status_arr[$v->status],
					'status_value' => $v->status,
					'reason' => $v->disagree_reason,
					'person_type' => $v->person_type > 0 ? $v->personType->name : '',
					'location' => RegionLogic::instance()->getRegionByChild($v->current_location),
					'create_time' => date('Y-m-d', $v->created_at),
					'need_test' => $v->need_test,
				];
			}
		}
	    
	    return [
	        'res' => $data,
	        'page' => BackLogic::instance()->pageFix($pagination)
	    ];
	}
	
	/**
	 * 录用
	 */
	public function employ($user)
	{
	    $talent = Talent::findOne($this->id);
	    $model = Employee::findOne(['phone'=>$talent->phone,'name'=>$talent->name]);
	    if(empty($model)){
	        $model = new Employee();
	        $model->name = $talent->name;
    	    $model->phone = $talent->phone;
    	    $model->profession = $talent->job;
    	    $model->birthday = $talent->birthday;
    	    $model->educational = $talent->educational;
    	    $model->work_time = $talent->work_time;
    	    $model->current_location = $talent->current_location;
    	    $model->age = $talent->age;
    	    $model->nation = $talent->nation;
    	    $model->native = $talent->native;
    	    $model->political = $talent->political;
    	    $model->marriage = $talent->marriage;
    	    $model->email = $talent->email;
    	    $model->status = 0;
    	    $model->employee_type = EmployeeType::findOne(['slug'=>'shiyong'])->id;
	    }
	    $model->org_id = $this->org_id;
	    $model->entry_time = $this->entry_time;
	    
	    $tran = yii::$app->db->beginTransaction();
        try {
            if (!$model->save()) { // 添加人事表
                throw new \Exception(current($model->getFirstErrors()));
            }
            $talent->status_face = $talent->status_face ?: 1;
            $talent->employee_id = $model->id;
            $talent->status = 5;
            if (! $talent->save()) { // 保存人才信息
                throw new \Exception(current($talent->getFirstErrors()));
            }
            
            foreach ($this->peopleModel as $v){
                $v::updateAll(['employee_id'=>$model->id],['talent_id'=>$talent->id]);
            }
            
            TalentLogic::instance()->addLog($this->id,'面试通过，录用',ArrayHelper::toArray($model),$user['person_name'],$user['person_id']);
            $tran->commit();
            return ['status'=>true];
	    }catch(\Exception $e){
	        $tran->rollBack();
	        return ['status'=>false,'msg'=>$e->getMessage()];
	    }
	    
	}
	
	
	/**
	 * 判断场景
	 */
	public function checkScenario()
	{
	    $model = Talent::findOne($this->id);
	    if(empty($model)){
	        $this->addError('','error');
	        return false;
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
	        case self::SCENARIO_EMPLOY://录用
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
	
	
	public function import($user)
	{
	    $file = $this->file;
	    
	    $arr = Excel::import($file->tempName, [
	        'setFirstRecordAsKeys' => false,
	        'setIndexSheetByName' => true,
	    ]);
	    array_shift($arr);
	    $error = [];//错误数组
	    $data = [];//插入oa_talent的数组
	    if($arr){
	        foreach($arr as &$v){
	            $res = $this->checkImportRow($v);
	            if(!$res['status']){
	                $error[] = $res['msg'];
	            }else{
	                $data[] = [
	                    $v['A'],
	                    $user['person_id'],
	                    $v['B'],
	                    $v['job'],
	                    $v['E'],
	                    $v['sex'],
	                    $v['edu'],
	                    $v['G'],
	                    time(),
	                    time(),
						$v['need_test'],
	                ];
	            }
	            unset($res);
	            unset($v);
	        }
	        if($error){
	            return ['status'=>false,'msg'=>implode(';', $error)];
	        }else{
	            $query = \Yii::$app->db->createCommand()->batchInsert('oa_talent',[
	                'name', 'owner', 'phone', 'job', 'age', 'sex', 'educational','work_time','created_at','updated_at','need_test'
	            ],$data);
	            $sql = $query->getRawSql();
	            $query->execute();
	            TalentLogic::instance()->addLog(0,'导入数据',$sql,$user['person_name'],$user['person_id']);
	            return ['status'=>true];
	        }
	    }
	    return ['status'=>false,'msg'=>'没有数据'];
	}
	
	protected function checkImportRow(&$row)
	{
	    //var_dump($row);die();
	    $error = [];
	    if(!$row['A']){
	        return ['status'=>false,'msg'=>':姓名不能为空'];
	    }
	    //手机号是否存在
	    $talent = Talent::findOne(['phone'=>$row['B']]);
	    if(empty($talent)){
	        $emp = Employee::findOne(['phone'=>$row['B']]);
	        if($emp){
	            $error[] = '已入职';
	        }
	    }else{
	        $error[] = '已存在招聘列表';
	    }
	    //手机号是否正确
	    if(!preg_match('/^1\d{10}$/', $row['B'])){
	        $error[] = '手机号不正确';
	    }
	    //职位
	    $profession = Job::findOne(['name'=>$row['C']]);
	    if(empty($profession)){
	        $error[] = '职位不存在';
	    }else{
	        $row['job'] = $profession->id;
			$row['need_test'] = $profession->need_exam;
	    }
	    //性别
	    if($row['D'] == '男'){
	        $row['sex'] = 2;
	    }elseif($row['D'] == '女'){
	        $row['sex'] = 1;
	    }else{
	        $error[] = '性别不正确';
	    }
	    //年龄
	    if($row['E'] <= 0){
	        $error[] = '年龄不正确';
	    }
	    //学历
	    $edu = Educational::findOne(['educational'=>$row['F']]);
	    if(empty($edu)){
	        $error[] = '学历不正确';
	    }else{
	        $row['edu'] = $edu->id;
	    }
	    //工作年限
	    if(strlen($row['G']) > 10){
	        $error[] = '工作年限不正确';
	    }
	    if($error){
	        return ['status'=>false,'msg'=>$row['A'].':'.implode(',', $error)];
	    }
	    return ['status'=>true];
	}
}