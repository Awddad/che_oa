<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/5
 * Time: 11:31
 */

namespace app\modules\oa_v1\controllers;


use app\modules\oa_v1\logic\BaseLogic;
use app\modules\oa_v1\models\LoanForm;

/**
 * 借款
 *
 * Class LoanController
 * @package app\modules\oa_v1\controllers
 */
class LoanController extends BaseController
{
    public function actionIndex()
    {
        $model = new LoanForm();

        $data['LoanForm'] = \Yii::$app->request->post();
        $user = $this->arrPersonInfo;
        if ($model->load($data) && $model->validate() && $applyId = $model->save($user)) {
            return $this->_return($applyId);
        } else {
            return $this->_returnError(4400, null, BaseLogic::instance()->getFirstError($model->errors));
        }
    }
}