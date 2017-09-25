<?php
/**
 * Created by PhpStorm.
 * User: YJR
 * Date: 2017/9/25
 * Time: 11:46
 */

namespace app\modules\oa_v1\controllers;

use Yii;
use app\modules\oa_v1\models\ApplyRetireForm;

class ApplyRetireController extends BaseController
{
    public function actionApply()
    {
        $post = Yii::$app->request->post();
        $data['ApplyRetireForm'] = $post;
        $model = new ApplyRetireForm();
        $model->setScenario($model::SCENARIO_APPLY);
        $model->load($data);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->saveApply($this->arrPersonInfo);
        if($res['status']){
            return $this->_return($res['apply_id']);
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }

    public function actionList()
    {
        
    }
}