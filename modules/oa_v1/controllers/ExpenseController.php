<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/4
 * Time: 10:39
 */

namespace app\modules\oa_v1\controllers;
use app\modules\oa_v1\models\ExpenseForm;
use yii\web\UploadedFile;


/**
 * Class ExpenseController
 * @package app\modules\oa_v1\controllers
 */
class ExpenseController extends BaseController
{
    /**
     * 报销申请
     */
    public function actionApply()
    {
        $form = new ExpenseForm();
        $data = [
            'ExpenseForm' => \Yii::$app->request->post()
        ];
        $data['ExpenseForm']['files']  = $form->saveUploadFile('files');
        $data['ExpenseForm']['pics']  = $form->saveUploadFile('pics');
        if($form->load($data) && $form->validate()&& $form->save()) {
            return $this->_return($form);
        } else {
            return $this->_return($form->errors, 400);
        }
        //return $this->_return([]);
    }
}