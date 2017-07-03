<?php
namespace app\modules\oa_v1\controllers;

use yii;
use app\modules\oa_v1\models\PeopleForm;

class PeopleController extends BaseController
{
    /**
     * 修改工作经验
     */
    public function actionWorkExpEdit()
    {
        $post = yii::$app->request->post();
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
}