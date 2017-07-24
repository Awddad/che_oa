<?php
namespace app\modules\oa_v1\controllers;


use yii;
use app\modules\oa_v1\models\TalentDemandForm;

class TalentDemandController extends BaseController
{
    /**
     * 编辑招聘需求
     */
    public function actionEditDemand()
    {
        $post = yii::$app->request->post();
        $model = new TalentDemandForm();
        $model->setScenario($model::SCENARIO_EDIT_DEMAND);
        $model->load(['TalentDemandForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->editDemand($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    /**
     * 操作
     */
    public function actionOperate()
    {
        $post = yii::$app->request->post();
        $model = new TalentDemandForm();
        switch($post['type']){
            case 0://未开始
                $model->setScenario($model::SCENARIO_DEMAND_START);
                break;
            case 1://进行中
                $model->setScenario($model::SCENARIO_DEMAND_ING);
                break;
            default:
                return $this->_returnError(403,'type不正确！');
        }
        $model->load(['TalentDemandForm'=>$post]);
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
     * 列表
     */
    public function actionGetList()
    {
        $get = yii::$app->request->get();
        $model = new TalentDemandForm();
        
        $data = $model->getList($get,$this->arrPersonInfo,$this->roleName);
        
        return $this->_return($data);
    }
    /**
     * 详情
     */
    public function actionGetInfo()
    {
        $id = yii::$app->request->get('id');
        if($id){
            $model = new TalentDemandForm();
            $res = $model->getInfo($id);
            if($res['status']){
                return $this->_return($res['data']);
            }else{
                return $this->_returnError(400,$res['msg']);
            }
        }
        return $this->_returnError(403,'id不能为空');
    }
}