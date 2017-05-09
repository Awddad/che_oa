<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/5
 * Time: 11:31
 */

namespace app\modules\oa_v1\controllers;


use app\modules\oa_v1\models\LoanForm;

/**
 * 还款
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
        $data['LoanForm']['pics']  = $model->saveUploadFile('pics');
        $user = $this->arrPersonInfo;
        if ($model->load($data) && $model->validate() && $model->save($user)) {
            return $this->_return($model);
        } else {
            return $this->_return($model->errors, 400);
        }
    }
}