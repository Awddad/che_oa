<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/9
 * Time: 15:54
 */

namespace app\modules\oa_v1\controllers;

use app\modules\oa_v1\logic\PayLogic;
use Yii;
use app\modules\oa_v1\models\PayConfirmForm;


/**
 * 付款确认
 *
 * Class PayConfirmController
 * @package app\modules\oa_v1\controllers
 */
class PayConfirmController extends BaseController
{
    /**
     * 付款确认
     */
    public function actionIndex()
    {
        $model = new PayConfirmForm();
        $post['PayConfirmForm'] = \Yii::$app->request->post();
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
        $pay = PayLogic::instance()->canConfirmList();
        return $this->_return($pay);
    }
}
