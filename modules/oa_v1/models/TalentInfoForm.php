<?php
namespace app\modules\oa_v1\models;

use app\models\Talent;
use app\modules\oa_v1\logic\PeopleLogic;
use app\models\Job;
use yii\helpers\ArrayHelper;
use app\models\Educational;
use app\models\Political;
use app\models\Region;
use app\modules\oa_v1\logic\RegionLogic;

class TalentInfoForm extends BaseForm
{
    const SCENARIO_TALENT_EDIT = 'talent_edit';//人才个人信息修改
    const SCENARIO_TALENT_YINGPIN_EDIT = 'talent_yingpin_edit';//人才应聘信息修改
    
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
    public $job_status;    
    public $location;    
    public $daogang;
    
    public $profession;
    public $want_salary;
    public $now_salary;
    
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
                ['id','name','sex','phone','birthday','email','age','edu','work_time','location','daogang'],
                'required',
                'on' => [self::SCENARIO_TALENT_EDIT],
                'message' => '{attribute}不能为空'
            ],
            [
                ['id','profession','location'],
                'required',
                'on' => [self::SCENARIO_TALENT_YINGPIN_EDIT],
                'message' => '{attribute}不能为空'
            ],
            ['id','exist','targetClass'=>'\app\models\Talent','message'=>'人才不存在','on'=>[self::SCENARIO_TALENT_EDIT]],
            ['profession','exist','targetClass'=>'\app\models\Job','targetAttribute'=>'id','message'=>'职位不存在'],
            ['sex','in', 'range' => [1, 2], 'message'=>'性别不正确'],
            ['phone','match','pattern'=>'/^1\d{10}/','message'=>'手机号不正确'],
            ['birthday','date','format' => 'yyyy-mm-dd','message' => '生日时间不正确'],
            ['email','email','message'=>'email不正确'],
            ['age', 'integer','message'=>'年龄不正确'],
            ['nation','string','max'=>15],//民族
            ['edu','exist','targetClass'=>'\app\models\Educational','targetAttribute'=>'id','message'=>'学历不存在'],
            ['political','exist','targetClass'=>'\app\models\Political','targetAttribute'=>'id','message'=>'政治面貌不存在'],
            ['native','string','max'=>15],//籍贯
            ['work_time','integer','message'=>'工作年限不正确'],
            ['marriage','in','range'=>[0,1,2,3],'message'=>'婚姻状况不正确'],
            ['job_status','in','range'=>[1,2,3,4],'message'=>'职业状态不正确'],
            ['location','exist','targetClass'=>'\app\models\Region','targetAttribute'=>'id','message'=>'当前所在地不正确！','on'=>[self::SCENARIO_TALENT_EDIT]],
            ['location','string','max'=>100,'on'=>[self::SCENARIO_TALENT_YINGPIN_EDIT]],//应聘地点
            ['daogang','string','max'=>20],//到岗时间
            [['want_salary','now_salary'],'string','max'=>10],//期望薪资，目前薪资
        ];
    }
    
    public function scenarios()
    {
        return [
            self::SCENARIO_TALENT_EDIT => ['id','name','sex','phone','birthday','email','age','nation','edu','political','native','work_time','marriage','job_status','location','daogang'],
            self::SCENARIO_TALENT_YINGPIN_EDIT => ['id','profession','want_salary','now_salary','location'],
        ];
    }
    /**
     * 保存人才基本信息
     * @param array $user
     */
    public function saveTalent($user)
    {
        $model = Talent::findOne($this->id);
        if(empty($model)){
            return ['status'=>false,'msg'=>'人才不存在'];
        }
        $model->name = $this->name;
        $model->sex = $this->sex;
        $model->phone = $this->phone;
        $model->email = $this->email;
        $model->birthday = $this->birthday;
        $model->age = $this->age;
        $model->work_time = $this->work_time;
        $model->educational = $this->edu;
        $model->current_location = $this->location;
        $model->daogang = $this->daogang;
        $this->native && $model->native = $this->native;
        $this->political && $model->political = $this->political;
        $this->nation && $model->nation = $this->nation;
        $this->job_status && $model->job_status = $this->job_status;
        if(!$model->save()){
            return ['status'=>false,'msg'=>current($this->getFirstErrors())]; 
        }else{
            PeopleLogic::instance()->addLog($model->id,0,'编辑人才个人信息',ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }
    }
    /**
     * 保存人才应聘信息
     * @param array $user
     */
    public function saveYingpin($user)
    {
        $model = Talent::findOne($this->id);
        if(empty($model)){
            return ['status'=>false,'msg'=>'人才不存在'];
        }
        $model->job = $this->profession;
        $model->yingpin_location = $this->location;
        $this->now_salary && $model->now_salary = $this->now_salary;
        $this->want_salary && $model->want_salary = $this->want_salary;
        if(!$model->save()){
            return ['status'=>false,'msg'=>current($this->getFirstErrors())];
        }else{
            PeopleLogic::instance()->addLog($model->id,0,'编辑人才招聘信息',ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }
    }
    
    /**
     * 获得人才个人信息
     * @param int $id
     * @return array
     */
    public function getTalentInfo($id)
    {
        $model = Talent::findOne($id);
        if(empty($model)){
            return ['status'=>false,'msg'=>'人才不存在'];
        }
        $data = [
            'id' => $model->id,
            'name' => $model->name,
            'sex' => $model->sex,
            'phone' => $model->phone,
            'email' => $model->email,
            'birthday' => $model->birthday,
            'age' => $model->age,
            'work_time' => $model->work_time,
            'educational_id' => $model->educational,
            'educational' => ($edu = Educational::findOne($model->educational)) ? $edu->educational : '',
            'location_id' => $model->current_location,
            'location' => RegionLogic::instance()->getRegionByChild($model->current_location),
            'location_info' => RegionLogic::instance()->getRegionIdByChild($model->current_location),
            'daogang' => $model->daogang,
            'native' => $model->native,
            'political_id' => $model->political,
            'political' => ($tmp = Political::findOne($model->political)) ? $tmp->political : '',
            'nation' => $model->nation,
            'job_status' => $model->job_status,
            'marriage' => $model->marriage,
            'status' => $this->status_arr[$model->status],
            'status_id' => $model->status
        ];
        return ['status'=>true,'data'=>$data];
    }
    /**
     * 获取应聘信息
     * @param int $id
     */
    public function getYingpin($id)
    {
        $model = Talent::findOne($id);
        if(empty($model)){
            return ['status'=>false,'msg'=>'人才不存在'];
        }
        $data = [
            'id' => $model->id,
            'profession' => empty($job = Job::findOne($model->job))?'':$job->name,
            'profession_id' => $model->job,
            'location' => $model->yingpin_location,
            'now_salary' => $model->now_salary,
            'want_salary' => $model->want_salary,
        ];
        return ['status'=>true,'data'=>$data];
    }
    
}