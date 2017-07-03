<?php
namespace app\modules\oa_v1\controllers;

use yii;
use app\modules\oa_v1\models\TalentForm;
use app\models\Educational;
use app\models\PersonType;
use app\modules\oa_v1\models\TalentInfoForm;

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
    
    /**
     * 列表
     */
    public function actionGetList()
    {
        $get = yii::$app->request->get();
        $model = new TalentForm();
        
        $data = $model->getList($get);
        
		return $this->_return($data);
    }
    
    /**
     * 获得学历
     */
    public function actionGetEdu()
    {
        $res = Educational::find()->all();
        $data = [];
        foreach($res as $v){
            $data[] = [
                'label' => $v['educational'],
                'value' => $v['id'],
            ];
        }
        return $this->_return($data);
    }
    
    
    /**
     * 获得人才类型
     */
    public function actionGetPersonType()
    {
        $res = PersonType::find()->all();
        $data = [];
        foreach($res as $v){
            $data[] = [
                'label' => $v['name'],
                'value' => $v['id'],
            ];
        }
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
}