<?php
/**
 * Created by PhpStorm.
 * User: YJR
 * Date: 2017/9/14
 * Time: 11:29
 */

namespace app\modules\oa_v1\controllers;

use app\modules\oa_v1\models\ApplyCertificateForm;
use Yii;

class ApplyCertificateController extends  BaseController
{
    public function actionAddApply()
    {
        $post = yii::$app->request->post();
        $model = new ApplyCertificateForm();
        $model->load(['ApplyCertificateForm'=>$post]);
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

    /**
     * 获得证件类型
     */
    public function actionGetType()
    {
        $model = new ApplyCertificateForm();
        return $this->_return($model->getCertificateType());
    }
}