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
     * 付款确认表单
     */
    public function actionForm()
    {
        $applyId = \Yii::$app->request->get('apply_id');
        $data = PayLogic::instance()->getForm($applyId, $this->arrPersonInfo);
        if(!$data) {
            return $this->_return($data, PayLogic::instance()->errorCode);
        }
        return $this->_return($data);
    }

    /**
     * 付款确认
     */
    public function actionIndex()
    {
        $model = new PayConfirmForm();
        $post['PayConfirmForm'] = \Yii::$app->request->post();
        $post['BackConfirmForm']['pics']  = $model->saveUploadFile('pics');
        if ($model->load($post) && $model->saveConfirm()) {
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
