<?php
namespace app\modules\oa_v1\controllers;

use yii;
use app\modules\oa_v1\models\ApprovalConfigForm;

class ApprovalConfigController extends BaseController
{
    /**
     * 编辑
     */
    public function actionEdit()
    {
        $post = yii::$app->request->post();
        $model = new ApprovalConfigForm();
        $model->setScenario($model::SCENARIO_EDIT);
        $model->load(['ApprovalConfigForm'=>$post]);
        if(!$model->validate() || !$model->checkApplyType($this->roleName)){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->edit($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    /**
     * 编辑审批人
     */
    public function actionEditApproval()
    {
        $post = yii::$app->request->post();
        $model = new ApprovalConfigForm();
        $model->setScenario($model::SCENARIO_APPROVAL_EDIT);
        $model->load(['ApprovalConfigForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->editApproval($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    /**
     * 编辑抄送人
     */
    public function actionEditCopyPerson()
    {
        $post = yii::$app->request->post();
        $model = new ApprovalConfigForm();
        $model->setScenario($model::SCENARIO_COPY_EDIT);
        $model->load(['ApprovalConfigForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->editCopyPerson($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    /**
     * 获得列表
     */
    public function actionGetList()
    {
        $get = yii::$app->request->get();
        $model = new ApprovalConfigForm();
        $res = $model->getList($get,$this->roleName);
        return $this->_return($res);
    }
    /**
     * 获得配置
     */
    public function actionGetConfig()
    {
        //$org_id = yii::$app->request->get('org_id');
        $apply_type = yii::$app->request->get('apply_type');
        if(!$apply_type){
            return $this->_returnError(403);
        }
        $model = new ApprovalConfigForm();
        $res = $model->getApprovalConfig($this->arrPersonInfo, $apply_type);
        return $this->_return($res);
    }
    /**
     * 获得详情
     */
    public function actionGetInfo()
    {
        $id = yii::$app->request->get('id');
        $model = new ApprovalConfigForm();
        $res = $model->getInfo($id);
        if(!$res['status']){
            return $this->_returnError(403,$res['msg']);
        }
        return $this->_return($res['data']);
    }
    /**
     * 获得可配置的审批类型
     */
    public function actionGetType()
    {
        $model = new ApprovalConfigForm();
        $res = $model->getApplyType($this->roleName);
        return $this->_return($res);
    }
}