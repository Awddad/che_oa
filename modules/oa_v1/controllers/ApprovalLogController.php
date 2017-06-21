<?php

namespace app\modules\oa_v1\controllers;

use app\models\Apply;
use app\modules\oa_v1\logic\ApprovalLogLogic;
use Yii;
use app\modules\oa_v1\logic\AfterApproval;

/**
 * 审批单
 *
 * Class ApprovalLogController
 * @package app\modules\oa_v1\controllers
 */
class ApprovalLogController extends BaseController
{
    /**
     * 1、修改当前审核记录的状态
     * 2、修改下一条【是否该我审核了】状态
     * 3、修改申请主表状态
     * 4、抄送人消息发送
     */
    public function actionUpdate()
    {
        $personId = Yii::$app->request->post('person_id');
        $applyId = Yii::$app->request->post('apply_id');
        $des = Yii::$app->request->post('des');
        $status = Yii::$app->request->post('status');

        $apply = Apply::findOne($applyId);
        if (!$apply) {
            return $this->_returnError(1010);
        }

        // 申请状态验证
        if (!in_array($apply->status, [Apply::STATUS_WAIT, Apply::STATUS_ING])) {
            return $this->_returnError(2001);
        }

        // 审批相关验证
        $approval = $apply->getNowApproval();
        if (!$approval) {
            return $this->_returnError(2404);
        }

        if ($approval->approval_person_id != $personId) {
            return $this->_returnError(2002);
        }

        /**
         * 审批
         * 1、审批不通过,修改申请表的状态
         * 2、审批通过
         * 2.1、需要继续审批
         * 2.2、需要财务确认
         * 2.3、申请全部完成
         */
        $approval->on($approval::EVENT_AFTER_UPDATE, [AfterApproval::instance(),'handler'],$apply->type);
        $approvalLogic = new ApprovalLogLogic($approval);
        $code = $approvalLogic->operate($status, $des);

        return $this->_returnError($code);
    }
}