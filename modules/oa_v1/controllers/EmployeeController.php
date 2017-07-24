<?php
namespace app\modules\oa_v1\controllers;

use app\modules\oa_v1\models\EmployeeForm;
use yii;
use app\modules\oa_v1\models\EmployeeInfoForm;
use app\modules\oa_v1\logic\AssetLogic;
use app\models\Employee;

/**
 * 员工
 * @author yjr
 *
 */
class EmployeeController extends BaseController
{
    /**
     * 获得员工列表
     */
    public function actionGetList()
    {
        $get = yii::$app->request->get();
        $model = new EmployeeForm();
        $data = $model->getList($get);
        return $this->_return($data);
    }
    
    /**
     * 添加员工
     */
    public function actionAddEmployee()
    {
        $post = yii::$app->request->post();
        $model = new EmployeeForm();
        $model->setScenario($model::SCENARIO_ADD_EMPLOYEE);
        $model->load(['EmployeeForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->addEmployee();
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    /**
     * 入职操作
     */
    public function actionEntry()
    {
        $post = yii::$app->request->post();
        $model = new EmployeeForm();
        $model->setScenario($model::SCENARIO_ENTRY);
        $model->load(['EmployeeForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->entry();
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    /**
     * 取消入职
     */
    public function actionCancel()
    {
        $post = yii::$app->request->post();
        $model = new EmployeeForm();
        $model->setScenario($model::SCENARIO_CANCEL);
        $model->load(['EmployeeForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->cancel();
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    /**
     * 获得员工基本信息
     */
    public function actionGetInfo()
    {
        $id = yii::$app->request->get('id');
        if($id){
            $model = new EmployeeInfoForm();
            $res = $model->getEmpInfo($id);
            if($res['status']){
                return $this->_return($res['data']);
            }else{
                return $this->_returnError(400,$res['msg']);
            }
        }
        return $this->_returnError(403,'id不能为空');
    }
    
    /**
     * 编辑员工基本信息
     */
    public function actionEditInfo()
    {
        $post = yii::$app->request->post();
        $model = new EmployeeInfoForm();
        $model->setScenario($model::SCENARIO_EMP_EDIT);
        $model->load(['EmployeeInfoForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->saveEmployee($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    
    
    /**
     * 获得员工帐号信息
     */
    public function actionGetAccount()
    {
        $id = yii::$app->request->get('id');
        if($id){
            $model = new EmployeeInfoForm();
            $res = $model->getAccount($id);
            if($res['status']){
                return $this->_return($res['data']);
            }else{
                return $this->_returnError(400,$res['msg']);
            }
        }
        return $this->_returnError(403,'id不能为空');
    }
    
    /**
     * 编辑员工帐号信息
     */
    public function actionEditAccount()
    {
        $post = yii::$app->request->post();
        $model = new EmployeeInfoForm();
        $model->setScenario($model::SCENARIO_EMP_ACCOUNT_EDIT);
        $model->load(['EmployeeInfoForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->saveAccount($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    /**
     * 获得员工银行卡信息
     */
    public function actionGetBank()
    {
        $id = yii::$app->request->get('id');
        if($id){
            $model = new EmployeeInfoForm();
            $res = $model->getBankCards($id);
            if($res['status']){
                return $this->_return($res['data']);
            }else{
                return $this->_returnError(400,$res['msg']);
            }
        }
        return $this->_returnError(403,'id不能为空');
    }
    /**
     * 删除银行卡
     */
    public function actionDelBank()
    {
        $post = yii::$app->request->post();
        $model = new EmployeeInfoForm();
        $model->setScenario($model::SCENARIO_EMP_BANK_DEL);
        $model->load(['EmployeeInfoForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->delBankCard($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    /**
     * 编辑银行卡
     */
    public function actionEditBank()
    {
        $post = yii::$app->request->post();
        $model = new EmployeeInfoForm();
        $model->setScenario($model::SCENARIO_EMP_BANK_EDIT);
        $model->load(['EmployeeInfoForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->saveBankCard($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    /**
     * 待归还资产
     * @return array
     */
    public function actionCanBackAsset()
    {
        $id = yii::$app->request->get('id');
        if($id && $emp = Employee::findOne($id)){
            $data = AssetLogic::instance()->getCanBackAsset($emp->person_id);
            return $this->_return($data);
        }
        return $this->_returnError(403,'id不正确');
    } 
}