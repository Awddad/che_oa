<?php
namespace app\modules\oa_v1\controllers;

use app\modules\oa_v1\models\EmployeeForm;
use yii;

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
}