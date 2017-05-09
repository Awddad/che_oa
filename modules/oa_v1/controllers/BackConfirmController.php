<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/9
 * Time: 16:01
 */

namespace app\modules\oa_v1\controllers;


use app\modules\oa_v1\logic\BackLogic;
use app\modules\oa_v1\models\BackConfirmForm;


/**
 * 还款确认
 *
 * Class BackConfirmController
 * @package app\modules\oa_v1\controllers
 */
class BackConfirmController extends BaseController
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $model = new BackConfirmForm();
        $post['BackConfirmForm'] = \Yii::$app->request->post();
        if ($model->load($post) && $model->save()) {
            return $this->_return('');
        } else {
            return $this->_return($model->errors, 400);
        }
    }


    /**
     * 付款确认列表
     */
    public function actionList()
    {
        $back = BackLogic::instance()->canConfirmList();
        return $this->_return($back);
    }

}