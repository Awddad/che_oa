<?php
namespace app\modules\oa_v1\controllers;

use app\modules\oa_v1\models\EmployeeForm;
use yii;
use app\modules\oa_v1\models\EmployeeInfoForm;

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
    
}