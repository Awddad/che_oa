<?php
namespace app\modules\oa_v1\models;

use app\models\Talent;
use app\modules\oa_v1\logic\PeopleLogic;
use yii\helpers\ArrayHelper;

class PeopleForm extends BaseForm
{
    const SCENARIO_WORK_EXP_EDIT = 'work_exp_edit';//修改工作经验
    const SCENARIO_WORK_EXP_DEL = 'work_exp_del';//删除工作经验
    const SCENARIO_WORK_EXP_GET = 'work_exp_get';//获得工作经验
    const SCENARIO_PROJECT_EXP_EDIT = 'project_exp_edit';//修改项目经验
    const SCENARIO_PROJECT_EXP_DEL = 'project_exp_del';//删除项目经验
    const SCENARIO_PROJECT_EXP_GET = 'project_exp_get';//获得项目经验
    const SCENARIO_EDU_EXP_EDIT = 'edu_exp_edit';//修改教育经验
    const SCENARIO_EDU_EXP_DEL = 'edu_exp_del';//删除教育经验
    const SCENARIO_EDU_EXP_GET = 'edu_exp_get';//获得教育经验
    const SCENARIO_ABILITY_EDIT = 'ability_edit';//修改能力评价
    const SCENARIO_ABILITY_GET = 'ability_get';//获得能力评价
    const SCENARIO_ABILITY_DEL = 'ability_del';//删除能力评价
    const SCENARIO_FILE_EDIT = 'file_edit';//修改能力评价
    const SCENARIO_FILE_GET = 'file_get';//获得能力评价
    const SCENARIO_FILE_DEL = 'file_del';//删除能力评价
    const SCENARIO_TRAIN_EXP_EDIT = 'train_exp_edit';//修改培训经验
    const SCENARIO_TRAIN_EXP_DEL = 'train_exp_del';//删除培训经验
    const SCENARIO_TRAIN_EXP_GET = 'train_exp_get';//获得培训经验
    
    public $id;
    public $company_name;
    public $start_time;
    public $end_time;
    public $profession;
    
    public $project_name;
    public $company_id;
    public $project_des;
    public $project_duty;
    public $school_name;
    public $major;
    public $edu;
    public $name;
    public $level;
    public $files;
    
    public $train_place;
    public $tran_content;
    
    public $talent;
    public $employee;
    
    
    public function rules()
    {
        return [
            [
                ['company_name','start_time','end_time','profession'],
                'required',
                'on'=>[self::SCENARIO_WORK_EXP_EDIT],
                'message' => '{attribute}不能为空'
            ],
            [
                ['project_name','profession','start_time','end_time'],
                'required',
                'on'=>[self::SCENARIO_PROJECT_EXP_EDIT],
                'message' => '{attribute}不能为空'
            ],
            [
                ['school_name','major','edu','start_time','end_time'],
                'required',
                'on'=>[self::SCENARIO_EDU_EXP_EDIT],
                'message' => '{attribute}不能为空'
            ],
            [
                ['name','level'],
                'required',
                'on' => [self::SCENARIO_ABILITY_EDIT],
                'message' => '{attribute}不能为空'
            ],
            [
                ['files'],
                'required',
                'on' => [self::SCENARIO_FILE_EDIT],
                'message' => '{attribute}不能为空'
            ],
            [
                ['train_place','start_time','end_time','tran_content'],
                'required',
                'on' => [self::SCENARIO_TRAIN_EXP_EDIT],
                'message' => '{attribute}不能为空'
            ],
            ['id','required','on'=>[self::SCENARIO_WORK_EXP_DEL],'message' => '{attribute}不能为空'],
            ['id','required','on'=>[self::SCENARIO_PROJECT_EXP_DEL],'message' => '{attribute}不能为空'],
            ['id','required','on'=>[self::SCENARIO_EDU_EXP_DEL],'message' => '{attribute}不能为空'],
            ['id','required','on'=>[self::SCENARIO_ABILITY_DEL],'message' => '{attribute}不能为空'],
            ['id','required','on'=>[self::SCENARIO_FILE_DEL],'message' => '{attribute}不能为空'],
            ['id','required','on'=>[self::SCENARIO_TRAIN_EXP_DEL],'message' => '{attribute}不能为空'],
            
            ['talent','exist','targetClass'=>'\app\models\Talent','targetAttribute'=>'id','message'=>'人不存在！'],
            ['employee','exist','targetClass'=>'\app\models\Employee','targetAttribute'=>'id','message'=>'员工不存在！'],
            ['start_time','date','format' => 'yyyy-mm','message' => '开始时间不正确'],
            ['end_time','date','format' => 'yyyy-mm','message' => '结束时间不正确'],
            ['company_id','exist','targetClass'=>'\app\models\PeopleWorkExperience','targetAttribute'=>'id','message'=>'公司不存在！'],
            ['edu','exist','targetClass'=>'\app\models\Educational','targetAttribute'=>'id','message'=>'学历不正确！'],
            ['name','string','max'=>20,'message'=>'技能名太长'],
            ['level','in', 'range' => [1, 2, 3, 4, 5], 'message'=>'技能等级不正确'],
            ['tran_content','string'],
            [
                'id',
                'exist',
                'targetClass' => '\app\models\PeopleWorkExperience',
                'on' => [
                    self::SCENARIO_WORK_EXP_EDIT,
                    self::SCENARIO_WORK_EXP_DEL,
                    self::SCENARIO_WORK_EXP_GET,
                ],
                'message' => '工作经验不存在！'
            ],
            [
                'id',
                'exist',
                'targetClass' => '\app\models\PeopleProjectExperience',
                'on' => [
                    self::SCENARIO_PROJECT_EXP_EDIT,
                    self::SCENARIO_PROJECT_EXP_GET,
                    self::SCENARIO_PROJECT_EXP_DEL
                ],
                'message' => '项目经验不存在！'
            ],
            [
                'id',
                'exist',
                'targetClass' => '\app\models\PeopleEduExperience',
                'on' => [
                    self::SCENARIO_EDU_EXP_EDIT,
                    self::SCENARIO_EDU_EXP_GET,
                    self::SCENARIO_EDU_EXP_DEL
                ],
                'message' => '教育经历不存在！'
            ],
            [
                'id',
                'exist',
                'targetClass' => '\app\models\PeopleAbility',
                'on' => [
                    self::SCENARIO_ABILITY_EDIT,
                    self::SCENARIO_ABILITY_GET,
                    self::SCENARIO_ABILITY_DEL
                ],
                'message' => '技能不存在！'
            ],
            [
                'id',
                'exist',
                'targetClass' => '\app\models\PeopleFiles',
                'on' => [
                    self::SCENARIO_FILE_EDIT,
                    self::SCENARIO_FILE_GET,
                    self::SCENARIO_FILE_DEL
                ],
                'message' => '文件不存在！'
            ],
            [
                'id',
                'exist',
                'targetClass' => '\app\models\PeopleTrainExperience',
                'on' => [
                    self::SCENARIO_TRAIN_EXP_EDIT,
                    self::SCENARIO_TRAIN_EXP_GET,
                    self::SCENARIO_TRAIN_EXP_DEL
                ],
                'message' => '培训经历不存在！'
            ],
        ];
    }
    
    public function scenarios()
    {
        return [
            self::SCENARIO_WORK_EXP_EDIT => ['id','talent','employee','company_name','start_time','end_time','profession'],
            self::SCENARIO_WORK_EXP_DEL => ['id','talent','employee'],
            self::SCENARIO_WORK_EXP_GET => ['id','talent','employee'],
            self::SCENARIO_PROJECT_EXP_EDIT => ['id','talent','employee','project_name','company_id','profession','start_time','end_time','project_des','project_duty'],
            self::SCENARIO_PROJECT_EXP_GET => ['id','talent','employee'],
            self::SCENARIO_PROJECT_EXP_DEL => ['id','talent','employee'],
            self::SCENARIO_EDU_EXP_EDIT => ['id','talent','employee','school_name','major','edu','start_time','end_time'],
            self::SCENARIO_EDU_EXP_DEL => ['id','talent','employee'],
            self::SCENARIO_EDU_EXP_GET => ['id','talent','employee'],
            self::SCENARIO_ABILITY_EDIT => ['id','talent','employee','name','level'],
            self::SCENARIO_ABILITY_GET => ['id','talent','employee'],
            self::SCENARIO_ABILITY_DEL => ['id','talent','employee'],
            self::SCENARIO_FILE_EDIT => ['id','talent','employee','files'],
            self::SCENARIO_FILE_GET => ['id','talent','employee'],
            self::SCENARIO_FILE_DEL => ['id','talent','employee'],
            self::SCENARIO_TRAIN_EXP_EDIT => ['id','talent','employee','train_place','start_time','end_time','tran_content'],
            self::SCENARIO_TRAIN_EXP_DEL => ['id','talent','employee'],
            self::SCENARIO_TRAIN_EXP_GET => ['id','talent','employee'],
        ];
    }
    /**
     * 修改工作经验
     * @param array $user
     */
    public function editWorkExp($user)
    {
        $model = $this->getModel('\app\models\PeopleWorkExperience');
        if(empty($model)){
            return ['status'=>false,'msg'=>'error'];
        }
        $model->company_name = $this->company_name;
        $model->start_time = $this->start_time;
        $model->end_time = $this->end_time;
        $model->profession = $this->profession;
        if(!$model->save()){
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
        }else{
            PeopleLogic::instance()->addLog($model->talent_id,$model->employee_id,'编辑工作经验',ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }
    }
    /**
     * 删除工作经验
     * @param array $user
     */
    public function delWorkExp($user)
    {
        $model = $this->getModel('\app\models\PeopleWorkExperience');
        if(empty($model)){
            return ['status'=>false,'msg'=>'error'];
        }
        if(!$model->delete()){
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
        }else{
            PeopleLogic::instance()->addLog($model->talent_id,$model->employee_id,'删除工作经验',ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }
    }
    
    /**
     * 获得工作经验
     */
    public function getWorkExp()
    {
        if($this->id){
            $res = \app\models\PeopleWorkExperience::findOne($this->id);
            $data = [];
            if($res){
                $data = $this->workExp($res);
            }
        }else{
            if($this->talent){
                $res = \app\models\PeopleWorkExperience::find()->where(['talent_id'=>$this->talent])->orderBy(['start_time'=>SORT_ASC])->all();
            }elseif($this->employee){
                $res = \app\models\PeopleWorkExperience::find()->where(['employee_id'=>$this->employee])->orderBy(['start_time'=>SORT_ASC])->all();
            }
            $data = [];
            if($res){
                foreach($res as $v){
                    $data[] = $this->workExp($v);
                }
            }
        }
        return $data;
        
    }
    /**
     * 格式化工作经验
     * @param \app\models\PeopleWorkExperience $model
     */
    protected function workExp($model)
    {
        return [
            'id' => $model->id,
            'company' => $model->company_name,//公司名
            'start_time' => $model->start_time,//开始时间
            'end_time' => $model->end_time,//结束时间
            'profession' => $model->profession,//职位
        ];
    }
    
    /**
     * 修改项目经验
     * @param array $user
     * @return array
     */
    public function editProjectExp($user)
    {
        $model = $this->getModel('\app\models\PeopleProjectExperience');
        if(empty($model)){
            return ['status'=>false,'msg'=>'error'];
        }
        $model->project_name = $this->project_name;
        $model->company_id = $this->company_id?:0;
        $model->project_profession = $this->profession;
        $model->project_des = $this->project_des;
        $model->project_duty = $this->project_duty;
        $model->start_time = $this->start_time;
        $model->end_time = $this->end_time;
        if(!$model->save()){
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
        }else{
            PeopleLogic::instance()->addLog($model->talent_id,$model->employee_id,'编辑项目经验',ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }
    }
    /**
     * 删除项目经验
     * @param array $user
     */
    public function delProjectExp($user)
    {
        $model = $this->getModel('\app\models\PeopleProjectExperience');
        if(empty($model)){
            return ['status'=>false,'msg'=>'error'];
        }
        if(!$model->delete()){
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
        }else{
            PeopleLogic::instance()->addLog($model->talent_id,$model->employee_id,'删除项目经验',ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }
    }
    /**
     * 获得项目经验
     */
    public function getProjectExp()
    {
        if($this->id){
            $res = \app\models\PeopleProjectExperience::findOne($this->id);
            $data = [];
            if($res){
                $data = $this->projectExp($res);
            }
        }else{
            if($this->talent){
                $res = \app\models\PeopleProjectExperience::find()->where(['talent_id'=>$this->talent])->orderBy(['start_time'=>SORT_ASC])->all();
            }elseif($this->employee){
                $res = \app\models\PeopleProjectExperience::find()->where(['employee_id'=>$this->employee])->orderBy(['start_time'=>SORT_ASC])->all();
            }
            $data = [];
            if($res){
                foreach($res as $v){
                    $data[] = $this->projectExp($v);
                }
            }
        }
        return $data;
    
    }
    /**
     * 格式化项目经验
     * @param \app\models\PeopleProjectExperience $model
     */
    protected function projectExp($model)
    {
        return [
            'id' => $model->id,
            'project_name' => $model->project_name,//项目名称
            'profession' => $model->project_profession,//项目职位
            'company' => $model->company_id ? $model->company->company_name : '',//公司名
            'company_id' => $model->company_id,
            'start_time' => $model->start_time,//开始时间
            'end_time' => $model->end_time,//结束时间
            'project_des' => $model->project_des,//项目简介
            'project_duty' => $model->project_duty,//项目责任
        ];
    }
    
    /**
     * 修改教育经历
     * @param array $user
     */
    public function editEduExp($user)
    {
        $model = $this->getModel('\app\models\PeopleEduExperience');
        if(empty($model)){
            return ['status'=>false,'msg'=>'error'];
        }
        $model->school_name = $this->school_name;
        $model->major = $this->major;
        $model->educational = $this->edu;
        $model->start_time = $this->start_time;
        $model->end_time = $this->end_time;
       
        if(!$model->save()){
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
        }else{
            PeopleLogic::instance()->addLog($model->talent_id,$model->employee_id,'编辑教育经历',ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }
    }
    /**
     * 删除教育经历
     * @param array $user
     */
    public function delEduExp($user)
    {
        $model = $this->getModel('\app\models\PeopleEduExperience');
        if(empty($model)){
            return ['status'=>false,'msg'=>'error'];
        }
        if(!$model->delete()){
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
        }else{
            PeopleLogic::instance()->addLog($model->talent_id,$model->employee_id,'删除教育经历',ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }
    }
    
    /**
     * 获得教育经历
     */
    public function getEduExp()
    {
        if($this->id){
            $res = \app\models\PeopleEduExperience::findOne($this->id);
            $data = [];
            if($res){
                $data = $this->eduExp($res);
            }
        }else{
            if($this->talent){
                $res = \app\models\PeopleEduExperience::find()->where(['talent_id'=>$this->talent])->orderBy(['start_time'=>SORT_ASC])->all();
            }elseif($this->employee){
                $res = \app\models\PeopleEduExperience::find()->where(['employee_id'=>$this->employee])->orderBy(['start_time'=>SORT_ASC])->all();
            }
            $data = [];
            if($res){
                foreach($res as $v){
                    $data[] = $this->eduExp($v);
                }
            }
        }
        return $data;
    }
    /**
     * 格式化教育经历
     * @param \app\models\PeopleEduExperience $model
     */
    protected function eduExp($model)
    {
        return [
            'id' => $model->id,
            'school_name' => $model->school_name,//学校名
            'major' => $model->major,//专业
            'start_time' => $model->start_time,//开始时间
            'end_time' => $model->end_time,//结束时间
            'educational' => $model->edu->educational,//学历
            'educational_id' => $model->educational,//学历id
        ];
    }
    
    /**
     * 修改技能评价
     * @param array $user
     */
    public function editAbility($user)
    {
        $model = $this->getModel('\app\models\PeopleAbility');
        if(empty($model)){
            return ['status'=>false,'msg'=>'error'];
        }
        $model->ability_name = $this->name;
        $model->level = $this->level;
         
        if(!$model->save()){
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
        }else{
            PeopleLogic::instance()->addLog($model->talent_id,$model->employee_id,'编辑技能评价',ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }
    }
    /**
     * 删除技能评价
     * @param array $user
     */
    public function delAbility($user)
    {
        $model = $this->getModel('\app\models\PeopleAbility');
        if(empty($model)){
            return ['status'=>false,'msg'=>'error'];
        }
        if(!$model->delete()){
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
        }else{
            PeopleLogic::instance()->addLog($model->talent_id,$model->employee_id,'删除技能评价',ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }
    }
    
    /**
     * 获得技能评价
     */
    public function getAbility()
    {
        if($this->id){
            $res = \app\models\PeopleAbility::findOne($this->id);
            $data = [];
            if($res){
                $data = $this->ability($res);
            }
        }else{
            if($this->talent){
                $res = \app\models\PeopleAbility::find()->where(['talent_id'=>$this->talent])->orderBy(['id'=>SORT_ASC])->all();
            }elseif($this->employee){
                $res = \app\models\PeopleAbility::find()->where(['employee_id'=>$this->employee])->orderBy(['id'=>SORT_ASC])->all();
            }
            $data = [];
            if($res){
                foreach($res as $v){
                    $data[] = $this->ability($v);
                }
            }
        }
        return $data;
    }
    /**
     * 格式化技能评价
     * @param \app\models\PeopleAbility $model
     */
    protected function ability($model)
    {
        return [
            'id' => $model->id,
            'name' => $model->ability_name,//技能名
            'level' => $model->level,//技能等级
        ];
    }
    
    /**
     * 修改文件
     * @param array $user
     */
    public function editFiles($user)
    {
        $model = $this->getModel('\app\models\PeopleFiles');
        if(empty($model)){
            return ['status'=>false,'msg'=>'error'];
        }
        $model->file = json_encode($this->files);
         
        if(!$model->save()){
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
        }else{
            PeopleLogic::instance()->addLog($model->talent_id,$model->employee_id,'编辑文件',ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }
    }
    /**
     * 删除文件
     * @param array $user
     */
    public function delFiles($user)
    {
        $model = $this->getModel('\app\models\PeopleFiles');
        if(empty($model)){
            return ['status'=>false,'msg'=>'error'];
        }
        if(!$model->delete()){
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
        }else{
            PeopleLogic::instance()->addLog($model->talent_id,$model->employee_id,'删除文件',ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }
    }
    
    /**
     * 获得文件
     */
    public function getFiles()
    {
        if($this->id){
            $res = \app\models\PeopleFiles::findOne($this->id);
            $data = [];
            if($res){
                $data = $this->files($res);
            }
        }else{
            if($this->talent){
                $res = \app\models\PeopleFiles::find()->where(['talent_id'=>$this->talent])->orderBy(['id'=>SORT_ASC])->all();
            }elseif($this->employee){
                $res = \app\models\PeopleFiles::find()->where(['employee_id'=>$this->employee])->orderBy(['id'=>SORT_ASC])->all();
            }
            $data = [];
            if($res){
                foreach($res as $v){
                    $data[] = $this->files($v);
                }
            }
        }
        return $data;
    }
    /**
     * 格式化文件
     * @param \app\models\PeopleFiles $model
     */
    protected function files($model)
    {
        $res = json_decode($model->file, true);//文件
        $res = is_array(current($res)) ? current($res) : $res;
        $res['id'] = $model->id;
        return $res;
    }
    
    
    
    /**
     * 修改培训经历
     * @param array $user
     */
    public function editTrainExp($user)
    {
        $model = $this->getModel('\app\models\PeopleTrainExperience');
        if(empty($model)){
            return ['status'=>false,'msg'=>'error'];
        }
        $model->train_place = $this->train_place;
        $model->start_time = $this->start_time;
        $model->end_time = $this->end_time;
        $model->tran_content = $this->tran_content;        
         
        if(!$model->save()){
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
        }else{
            PeopleLogic::instance()->addLog($model->talent_id,$model->employee_id,'编辑培训经历',ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }
    }
    /**
     * 删除培训经历
     * @param array $user
     */
    public function delTrainExp($user)
    {
        $model = $this->getModel('\app\models\PeopleTrainExperience');
        if(empty($model)){
            return ['status'=>false,'msg'=>'error'];
        }
        if(!$model->delete()){
            return ['status'=>false,'msg'=>current($model->getFirstErrors())];
        }else{
            PeopleLogic::instance()->addLog($model->talent_id,$model->employee_id,'删除培训经历',ArrayHelper::toArray($model),$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }
    }
    
    /**
     * 获得培训经历
     */
    public function getTrainExp()
    {
        if($this->id){
            $res = \app\models\PeopleTrainExperience::findOne($this->id);
            $data = [];
            if($res){
                $data = $this->trainExp($res);
            }
        }else{
            if($this->talent){
                $res = \app\models\PeopleTrainExperience::find()->where(['talent_id'=>$this->talent])->orderBy(['id'=>SORT_ASC])->all();
            }elseif($this->employee){
                $res = \app\models\PeopleTrainExperience::find()->where(['employee_id'=>$this->employee])->orderBy(['id'=>SORT_ASC])->all();
            }
            $data = [];
            if($res){
                foreach($res as $v){
                    $data[] = $this->trainExp($v);
                }
            }
        }
        return $data;
    }
    /**
     * 格式化培训经历
     * @param \app\models\PeopleFiles $model
     */
    protected function trainExp($model)
    {
        return [
            'id'=>$model->id,
            'train_place'=>$model->train_place,//培训地点
            'tran_content'=>$model->tran_content,//培训内容
            'start_time' => $model->start_time,//开始时间
            'end_time' => $model->end_time,//结束时间
        ];
    }
    
    
    
    /**
     * 获得model
     * @param string $class_name
     */
    protected function getModel($class_name)
    {
        if(!class_exists($class_name)){
            return false;
        }
        if($this->talent){
            return $this->getModelByTalent($class_name, $this->talent);
        }elseif($this->employee){
            return $this->getModelByEmployee($class_name, $this->employee);
        }
        return false;
    }
    
    protected function getModelByTalent($class_name,$talent_id)
    {
        if($this->id){
            $model = $class_name::findOne($this->id);
            if(!$model || $model->talent_id != $talent_id){
                return false;
            }
        }else{
            $model = new $class_name();
        }
        $talent = Talent::findOne($talent_id);
        $model->talent_id = $talent_id;
        $model->employee_id = $talent->employee_id;
        return $model;
    }
    
    protected function getModelByEmployee($class_name,$employee_id)
    {
        if($this->id){
            $model = $class_name::findOne($this->id);
            if(!$model || $model->employee_id != $employee_id){
                return false;
            }
        }else{
            $model = new $class_name();
        }
        $talent = Talent::findOne(['employee_id'=>$employee_id]);
        $model->talent_id = empty($talent) ? 0 : $talent->id;
        $model->employee_id = $employee_id;
        return $model;
    }
    
    /**
     * 判断talent和employee必有其一
     * @return boolean
     */
    public function checkPeople()
    {
        if($this->talent || $this->employee){
            return true;
        }
        $this->addError('','talent和employee必有其一');
        return false;
    }
}