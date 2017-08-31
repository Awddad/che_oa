<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/8/31
 * Time: 13:11
 */

namespace app\modules\oa_v1\controllers;


use app\modules\oa_v1\logic\BaseLogic;
use app\modules\oa_v1\models\ApplyProjectRoleForm;

/**
 * 权限申请
 *
 * Class ApplyProjectController
 * @package app\modules\oa_v1\controllers
 */
class ApplyProjectController extends BaseController
{
    /**
     * @return array
     */
    public function actionRole()
    {
        $form = new ApplyProjectRoleForm();
        $form->load(['ApplyProjectRoleForm' => \Yii::$app->request->post()]);
        if (!$form->validate()) {
            return $this->_returnError(4400, null, BaseLogic::instance()->getFirstError($form->errors));
        }
        if ($rst = $form->save($this->arrPersonInfo)) {
            return $this->_return($rst);
        }
        return $this->_returnError(4400, null, '创建失败');
    }
}