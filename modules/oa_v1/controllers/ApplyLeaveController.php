<?php
namespace app\modules\oa_v1\controllers;

use yii;
use app\modules\oa_v1\models\ApplyLeaveForm;

class ApplyLeaveController extends BaseController
{
    public function actionAddApply()
    {
        $post = yii::$app->request->post();
        $model = new ApplyLeaveForm();
        $model->load(['ApplyLeaveForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->saveApply($this->arrPersonInfo);
        if($res['status']){
            return $this->_return($res['apply_id']);
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
}