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
        $data = PayLogic::instance()->getForm($applyId, $this->arrPersonInfo, $this->companyIds);
        if(!$data) {
            return $this->_returnError(PayLogic::instance()->errorCode, $data);
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
        if ($model->load($post) && $model->validate() && $model->saveConfirm($this->arrPersonInfo)) {
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
        if(isset($this->arrPersonRoleInfo['permissionOrgIds'])) {
            $pay = PayLogic::instance()->canConfirmList($this->arrPersonRoleInfo['permissionOrgIds']);
            return $this->_return($pay);
        } else {
            return $this->_returnError(403);
        }
    }

    /**
     * 导出付款确认列表
     */
    public function actionExport()
    {
        PayLogic::instance()->export($this->arrPersonRoleInfo['permissionOrgIds']);
    }
}
