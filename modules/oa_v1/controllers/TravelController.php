<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/8/24
 * Time: 11:06
 */

namespace app\modules\oa_v1\controllers;

use app\modules\oa_v1\logic\BaseLogic;
use app\modules\oa_v1\models\TravelForm;

/**
 * Class TravelController
 * @package app\modules\oa_v1\controllers
 */
class TravelController extends BaseController
{
    public function actionIndex()
    {
        $form = new TravelForm();
        $form->load(['TravelForm' => \Yii::$app->request->post()]);
        if (!$form->validate()) {
            return $this->_returnError(4400, null, BaseLogic::instance()->getFirstError($form->errors));
        }
        if ($rst = $form->save($this->arrPersonInfo)) {
            return $this->_return($rst);
        }
        return $this->_returnError(4400, null, '创建失败');
    }
}