<?php
namespace app\modules\oa_v1\controllers;

use yii;
use app\modules\oa_v1\models\TalentForm;
use app\models\Educational;
use app\models\PersonType;

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
}