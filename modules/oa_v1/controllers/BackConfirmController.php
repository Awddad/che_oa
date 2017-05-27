<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/9
 * Time: 16:01
 */

namespace app\modules\oa_v1\controllers;


use app\models\Apply;
use app\models\CaiWuFuKuan;
use app\modules\oa_v1\logic\BackLogic;
use app\modules\oa_v1\logic\PayLogic;
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
     * 还款表单数据
     */
    public function actionForm()
    {
        $applyId = \Yii::$app->request->get('apply_id');
        $data = BackLogic::instance()->backForm($applyId, $this->arrPersonInfo);
        if(!$data) {
            return $this->_return($data, BackLogic::instance()->errorCode);
        }
        return $this->_return($data);
    }

    /**
     * 还款确认
     *
     * @return array
     */
    public function actionIndex()
    {
        $applyId = \Yii::$app->request->post('apply_id');
        $caiwu = CaiWuFuKuan::findOne($applyId);
        if($caiwu){
            return $this->_return('', 1010, '已确认');
        }
        $model = new BackConfirmForm();
        $post['BackConfirmForm'] = \Yii::$app->request->post();
//        $files = $model->saveUploadFile('pics');
//        if($files) {
//            $data['BackConfirmForm']['pics']  = $files;
//        }
        if ($model->load($post) &&  $model->validate() && $model->saveConfirm($this->arrPersonInfo)) {
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
        $back = BackLogic::instance()->canConfirmList($this->arrPersonRoleInfo['permissionOrgIds']);
        return $this->_return($back);
    }

    /**
     * 导出付款确认列表
     */
    public function actionExport()
    {
        BackLogic::instance()->export($this->arrPersonInfo, $this->arrPersonRoleInfo['permissionOrgIds']);
    }

}