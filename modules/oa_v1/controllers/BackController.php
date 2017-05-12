<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/9
 * Time: 10:57
 */

namespace app\modules\oa_v1\controllers;


use app\modules\oa_v1\logic\BackLogic;
use app\modules\oa_v1\models\BackForm;
use Yii;


/**
 * 借款逻辑
 * Class BackController
 * @package app\modules\oa_v1\controllers
 */
class BackController extends BaseController
{
    /**
     * 可以还款的借款
     *
     * @return array
     */
    public function actionCanBack()
    {
        $back = BackLogic::instance()->getCanBack($this->arrPersonInfo);
        return $this->_return($back);
    }

    /**
     * 还款申请
     *
     *
     * @return array
     */
    public function actionIndex()
    {
        $model = new BackForm();

        $data['BackForm'] = \Yii::$app->request->post();
        $user = $this->arrPersonInfo;
        if ($model->load($data) && $model->validate() && $applyId = $model->save($user)) {
            return $this->_return($applyId);
        } else {
            return $this->_return($model->errors, 400);
        }
    }

}