<?php
namespace app\modules\oa_v1\controllers;


use app\models\Person;
use app\models\TalentDemand;
use moonland\phpexcel\Excel;
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
    
    /**
     * 招聘需求导出
     */
    public function actionExport()
    {
        $query = TalentDemand::find();
    
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
                'org_name',
                'profession',
                'number',
                'sex',
                'edu',
                'work_time',
                [
                    'attribute' => 'status',
                    'value' => function($data){
                        return TalentDemand::STATUS[$data->status];
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
            'fileName' => '招聘需求'.date('YmdHis'),
            'format' => 'Excel2007'
        ]);
    }
}