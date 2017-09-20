<?php
/**
 * Created by PhpStorm.
 * User: YJR
 * Date: 2017/9/14
 * Time: 17:14
 */

namespace app\modules\oa_v1\controllers;

use Yii;
use app\modules\oa_v1\models\ApplyHolidayForm;

class ApplyHolidayController extends BaseController
{
    public function actionAddApply()
    {
        $post = Yii::$app->request->post();
        $model = new ApplyHolidayForm();
        $model->load(['ApplyHolidayForm'=>$post]);
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
        $model = new ApplyHolidayForm();
        return $this->_return($model->getHolidayType());
    }
}