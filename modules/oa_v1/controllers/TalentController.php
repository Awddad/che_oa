<?php
namespace app\modules\oa_v1\controllers;

use app\models\Educational;
use app\models\Job;
use app\models\Person;
use app\models\Talent;
use app\modules\oa_v1\logic\RegionLogic;
use moonland\phpexcel\Excel;
use yii;
use app\modules\oa_v1\models\TalentForm;
use app\modules\oa_v1\models\TalentInfoForm;
use yii\web\UploadedFile;

/**
 * 招聘 人才
 * @author yjr
 *
 */
class TalentController extends BaseController
{
    public function actionAddZhaopin()
    {
        $post = yii::$app->request->post();
        $model = new TalentForm();
        $model->setScenario($model::SCENARIO_ADD_ZHAOPIN);
        $model->load(['TalentForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->addTalent($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    /**
     * 审核
     */
    public function actionApproval()
    {
        $post = yii::$app->request->post();
        $model = new TalentForm();
        switch($post['type']){
            case 1://待沟通
                $model->setScenario($model::SCENARIO_COMMUNION);
                break;
            case 2://待考试
                $model->setScenario($model::SCENARIO_TEST);
                break;
            case 3://待面试
                $model->setScenario($model::SCENARIO_FACE);
                break;
            default:
                return $this->_returnError(403,'type不正确！');
        }
        $model->load(['TalentForm'=>$post]);
        if(!$model->validate() || !$model->checkScenario()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->operate($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    /**
     * 移入人才库
     */
    public function actionJoinTalent()
    {
        $post = yii::$app->request->post();
        $model = new TalentForm();
        $model->setScenario($model::SCENARIO_JOIN);
        $model->load(['TalentForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->joinTalent($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    /**
     * 录用
     */
    /*
    public function actionEmploy()
    {
        $post = yii::$app->request->post();
        $model = new TalentForm();
        $model->setScenario($model::SCENARIO_EMPLOY);
        $model->load(['TalentForm'=>$post]);
        if(!$model->validate() || !$model->checkScenario()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->employ($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    */
    
    /**
     * 列表
     */
    public function actionGetList()
    {
        $get = yii::$app->request->get();
        $model = new TalentForm();
        
        $data = $model->getList($get,$this->arrPersonInfo,$this->roleName);
        
		return $this->_return($data);
    }
    
    
    /**
     * 修改人才个人信息
     */
    public function actionEditInfo()
    {
        $post = yii::$app->request->post();
        $model = new TalentInfoForm();
        $model->setScenario($model::SCENARIO_TALENT_EDIT);
        $model->load(['TalentInfoForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->saveTalent($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    /**
     * 修改人才应聘信息
     */
    public function actionEditYingpin()
    {
        $post = yii::$app->request->post();
        $model = new TalentInfoForm();
        $model->setScenario($model::SCENARIO_TALENT_YINGPIN_EDIT);
        $model->load(['TalentInfoForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->saveYingpin($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    /**
     * 获得人才个人信息
     */
    public function actionGetInfo()
    {
        $id = yii::$app->request->get('id');
        if($id){
            $model = new TalentInfoForm();
            $res = $model->getTalentInfo($id);
            if($res['status']){
                return $this->_return($res['data']);
            }else{
                return $this->_returnError(400,$res['msg']);
            }
        }
        return $this->_returnError(403,'id不能为空');
    }
    /**
     * 获得人才应聘信息
     */
    public function actionGetYingpin()
    {
        $id = yii::$app->request->get('id');
        if($id){
            $model = new TalentInfoForm();
            $res = $model->getYingpin($id);
            if($res['status']){
                return $this->_return($res['data']);
            }else{
                return $this->_returnError(400,$res['msg']);
            }
        }
        return $this->_returnError(403,'id不能为空');
    }
    /**
     * 导入excel
     */
    public function actionImport()
    {
        $file = UploadedFile::getInstanceByName('file');
        $model = new TalentForm();
        $model->setScenario($model::SCENARIO_IMPORT);
        $model->file = $file;
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->import($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }

    /**
     * 获得曾经
     */
    public function actionGetScore()
    {
        $id = yii::$app->request->get('id');
        if($id){
            $model = new TalentInfoForm();
            $res = $model->getScore($id);
            if($res['status']){
                return $this->_return($res['data']);
            }else{
                return $this->_returnError(400,$res['msg']);
            }
        }
        return $this->_returnError(403,'id不能为空');
    }

    /**
     * 编辑成绩
     */
    public function actionEditScore()
    {
        $post = yii::$app->request->post();
        $model = new TalentInfoForm();
        $model->setScenario($model::SCENARIO_TALENT_SCORE_EDIT);
        $model->load(['TalentInfoForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->saveScore($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    /**
     * 招聘导出
     */
    public function actionExport()
    {
        $query = Talent::find();
    
        $start_time = Yii::$app->request->get('start_time');
        $end_time = Yii::$app->request->get('end_time');
        if ($start_time && $end_time) {
            $query->where([
                'and',
                ['>', 'created_at', strtotime($start_time)],
                ['<=', 'created_at', strtotime('+1day', strtotime('end_time'))],
            ]);
        }
        if ($keywords = trim(Yii::$app->request->get('keywords'))) {
            $query->andWhere("instr(CONCAT(profession,org_name),'{$keywords}') > 0 ");
        }
        Excel::export([
            'models' => $query->all(),
            'columns' => [
                'name',
                'phone',
                [
                    'attribute' => 'job',
                    'value' => function($data){
                        return Job::findOne($data->job)->name;
                    }
                ],
                [
                    'attribute' => 'sex',
                    'value' => function($data){
                        return $data->sex == 1 ? '女' : '男';
                    }
    
                ],
                'age',
                [
                    'attribute' => 'educational',
                    'value' => function($data){
                        return Educational::findOne($data->educational)->educational;
                    }
                ],
                'work_time',
                [
                    'attribute' => 'current_location',
                    'value' => function($data){
                        return RegionLogic::instance()->getRegionByChild($data->current_location);
                    }
                ],
                [
                    'attribute' => 'status',
                    'value' => function($data){
                        return Talent::STATUS[$data->status];
                    }
            
                ],
                [
                    'attribute' => 'owner',
                    'value' => function($data){
                        $person = Person::findOne($data->owner);
                        if ($person) {
                            return $person->person_name;
                        }
                        return '--';
                    }
            
                ],
            ],
            'fileName' => '招聘列表'.date('YmdHis'),
            'format' => 'Excel2007'
        ]);
    }
}