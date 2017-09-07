<?php
namespace app\modules\oa_v1\controllers;

use Yii;
use app\modules\oa_v1\models\PeopleForm;

class PeopleController extends BaseController
{
    public function beforeAction($action)
    {
        if(Yii::$app->request->get('edit_myself')) {
            $employee = Yii::$app->request->get('employee') ? Yii::$app->request->get('employee') : Yii::$app->request->post('employee');
            if ($this->arrPersonInfo->employee->id != $employee) {
                return $this->_returnError(400);
            }
        }
        return parent::beforeAction($action);
    }
    
    /**
     * 修改工作经验
     */
    public function actionWorkExpEdit()
    {
        $post = Yii::$app->request->post();
        $model = new PeopleForm();
        $model->setScenario($model::SCENARIO_WORK_EXP_EDIT);
        $model->load(['PeopleForm'=>$post]);
        if(!$model->checkPeople() || !$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->editWorkExp($this->arrPersonInfo);
        if($res['status']){
			return $this->_return('成功');
		}else{
			return $this->_returnError(400,$res['msg']);
		}
    }
    /**
     * 获取工作经验
     */
    public function actionWorkExpGet()
    {
        $get = yii::$app->request->get();
        $model = new PeopleForm();
        $model->setScenario($model::SCENARIO_WORK_EXP_GET);
        $model->load(['PeopleForm'=>$get]);
        if(!$model->checkPeople() || !$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $data = $model->getWorkExp($this->arrPersonInfo);
        
        return $this->_return($data);
    }
    /**
     * 删除工作经验
     */
    public function actionWorkExpDel()
    {
        $post = yii::$app->request->post();
        $model = new PeopleForm();
        $model->setScenario($model::SCENARIO_WORK_EXP_DEL);
        $model->load(['PeopleForm'=>$post]);
        if(!$model->checkPeople() || !$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->delWorkExp($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    /**
     * 修改项目经验
     */
    public function actionProjectExpEdit()
    {
        $post = yii::$app->request->post();
        $model = new PeopleForm();
        $model->setScenario($model::SCENARIO_PROJECT_EXP_EDIT);
        $model->load(['PeopleForm'=>$post]);
        if(!$model->checkPeople() || !$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->editProjectExp($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    /**
     * 获取项目经验
     */
    public function actionProjectExpGet()
    {
        $get = yii::$app->request->get();
        $model = new PeopleForm();
        $model->setScenario($model::SCENARIO_PROJECT_EXP_GET);
        $model->load(['PeopleForm'=>$get]);
        if(!$model->checkPeople() || !$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $data = $model->getProjectExp($this->arrPersonInfo);
    
        return $this->_return($data);
    }
    /**
     * 删除项目经验
     */
    public function actionProjectExpDel()
    {
        $post = yii::$app->request->post();
        $model = new PeopleForm();
        $model->setScenario($model::SCENARIO_PROJECT_EXP_DEL);
        $model->load(['PeopleForm'=>$post]);
        if(!$model->checkPeople() || !$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->delProjectExp($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    /**
     * 修改教育经历
     */
    public function actionEduExpEdit()
    {
        $post = yii::$app->request->post();
        $model = new PeopleForm();
        $model->setScenario($model::SCENARIO_EDU_EXP_EDIT);
        $model->load(['PeopleForm'=>$post]);
        if(!$model->checkPeople() || !$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->editEduExp($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    /**
     * 获取教育经历
     */
    public function actionEduExpGet()
    {
        $get = yii::$app->request->get();
        $model = new PeopleForm();
        $model->setScenario($model::SCENARIO_EDU_EXP_GET);
        $model->load(['PeopleForm'=>$get]);
        if(!$model->checkPeople() || !$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $data = $model->getEduExp($this->arrPersonInfo);
    
        return $this->_return($data);
    }
    /**
     * 删除教育经历
     */
    public function actionEduExpDel()
    {
        $post = yii::$app->request->post();
        $model = new PeopleForm();
        $model->setScenario($model::SCENARIO_EDU_EXP_DEL);
        $model->load(['PeopleForm'=>$post]);
        if(!$model->checkPeople() || !$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->delEduExp($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    /**
     * 修改技能评价
     */
    public function actionAbilityEdit()
    {
        $post = yii::$app->request->post();
        $model = new PeopleForm();
        $model->setScenario($model::SCENARIO_ABILITY_EDIT);
        $model->load(['PeopleForm'=>$post]);
        if(!$model->checkPeople() || !$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->editAbility($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    /**
     * 获取技能评价
     */
    public function actionAbilityGet()
    {
        $get = yii::$app->request->get();
        $model = new PeopleForm();
        $model->setScenario($model::SCENARIO_ABILITY_GET);
        $model->load(['PeopleForm'=>$get]);
        if(!$model->checkPeople() || !$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $data = $model->getAbility($this->arrPersonInfo);
    
        return $this->_return($data);
    }
    /**
     * 删除技能评价
     */
    public function actionAbilityDel()
    {
        $post = yii::$app->request->post();
        $model = new PeopleForm();
        $model->setScenario($model::SCENARIO_ABILITY_DEL);
        $model->load(['PeopleForm'=>$post]);
        if(!$model->checkPeople() || !$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->delAbility($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    /**
     * 修改文件
     */
    public function actionFileEdit()
    {
        $post = yii::$app->request->post();
        $model = new PeopleForm();
        $model->setScenario($model::SCENARIO_FILE_EDIT);
        $model->load(['PeopleForm'=>$post]);
        if(!$model->checkPeople() || !$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->editFiles($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    /**
     * 获取文件
     */
    public function actionFileGet()
    {
        $get = yii::$app->request->get();
        $model = new PeopleForm();
        $model->setScenario($model::SCENARIO_FILE_GET);
        $model->load(['PeopleForm'=>$get]);
        if(!$model->checkPeople() || !$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $data = $model->getFiles($this->arrPersonInfo);
    
        return $this->_return($data);
    }
    /**
     * 删除文件
     */
    public function actionFileDel()
    {
        $post = yii::$app->request->post();
        $model = new PeopleForm();
        $model->setScenario($model::SCENARIO_FILE_DEL);
        $model->load(['PeopleForm'=>$post]);
        if(!$model->checkPeople() || !$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->delFiles($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    
    /**
     * 修改培训经历
     */
    public function actionTrainExpEdit()
    {
        $post = yii::$app->request->post();
        $model = new PeopleForm();
        $model->setScenario($model::SCENARIO_TRAIN_EXP_EDIT);
        $model->load(['PeopleForm'=>$post]);
        if(!$model->checkPeople() || !$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->editTrainExp($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    /**
     * 获取培训经历
     */
    public function actionTrainExpGet()
    {
        $get = yii::$app->request->get();
        $model = new PeopleForm();
        $model->setScenario($model::SCENARIO_TRAIN_EXP_GET);
        $model->load(['PeopleForm'=>$get]);
        if(!$model->checkPeople() || !$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $data = $model->getTrainExp($this->arrPersonInfo);
    
        return $this->_return($data);
    }
    /**
     * 删除培训经历
     */
    public function actionTrainExpDel()
    {
        $post = yii::$app->request->post();
        $model = new PeopleForm();
        $model->setScenario($model::SCENARIO_TRAIN_EXP_DEL);
        $model->load(['PeopleForm'=>$post]);
        if(!$model->checkPeople() || !$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->delTrainExp($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    
    /**
     * 修改头像
     */
    public function actionPicEdit()
    {
        $post = yii::$app->request->post();
        $model = new PeopleForm();
        $model->setScenario($model::SCENARIO_PIC_EDIT);
        $model->load(['PeopleForm'=>$post]);
        if(!$model->checkPeople() || !$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->editPic($this->arrPersonInfo);
        if($res['status']){
            return $this->_return($res['id']);
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    /**
     * 获取头像
     */
    public function actionPicGet()
    {
        $get = yii::$app->request->get();
        $model = new PeopleForm();
        $model->setScenario($model::SCENARIO_PIC_GET);
        $model->load(['PeopleForm'=>$get]);
        if(!$model->checkPeople() || !$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $data = $model->getPic($this->arrPersonInfo);
    
        return $this->_return($data);
    }
    /**
     * 删除头像
     */
    public function actionPicDel()
    {
        $post = yii::$app->request->post();
        $model = new PeopleForm();
        $model->setScenario($model::SCENARIO_PIC_DEL);
        $model->load(['PeopleForm'=>$post]);
        if(!$model->checkPeople() || !$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->delPic($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
}