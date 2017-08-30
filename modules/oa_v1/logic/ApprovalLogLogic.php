<?php

namespace app\modules\oa_v1\logic;

use app\models\ApplyCopyPerson;
use Yii;
use app\models\ApprovalLog;

/**
 * 审批逻辑层
 *
 * @property ApprovalLog $object
 *
 * @package app\modules\oa_v1\logic
 */
class ApprovalLogLogic extends BaseLogic
{
    private $object;

    public function __construct($object)
    {
        parent::__construct();
        $this->object = $object;
    }

    /**
     * 审批操作
     *
     * 1、添加审批备注
     * 2、修改我审批的状态
     *
     * @param $status
     * @param $des
     * @return int
     */
    public function operate($status, $des)
    {
        $db = Yii::$app->db->beginTransaction();
        $this->object->des = $des;
        $this->object->is_to_me_now = 0;
        $this->object->approval_time = time();

        if ($status) {
            $result = $this->_pass();
        } else {
            $result = $this->_fail();
        }

        if ($result) {
            $code = 200;
            $db->commit();
        } else {
            $code = 404;
            $db->rollBack();
        }
        return $code;
    }

    /**
     * 审批不通过
     *
     * 1、更新审批状态
     * 2、更新申请状态
     *
     * @return bool
     */
    private function _fail()
    {
        $this->object->scenario = ApprovalLog::SCENARIO_FAIL;
        $this->object->result = ApprovalLog::STATUS_FAIL;

        return $this->object->save();
    }

    /**
     * 审批通过
     *
     * 1、有下一条审批
     *  1.1、更新当前审批记录
     *  1.2、更新下一条审批记录
     *  1.3、更新申请的记录
     *
     * 2、没有下一条审批
     *  2.1、不需要财务审批
     *      2.1.1、更新当前审批记录
     *      2.1.2、更新申请记录
     *  2.2、需要财务审批
     *      2.2.1、更新申请记录，需要财务确认
     *      2.2.2、更新当前审批记录
     *
     * @return bool
     */
    private function _pass()
    {
        $this->object->result = ApprovalLog::STATUS_PASS;

        $nextApproval = $this->object->getNextApprovalLog();
        if ($nextApproval) {
            $this->object->scenario = ApprovalLog::SCENARIO_PASS;
            return $this->object->save();
        } else {
            ApplyCopyPerson::updateAll(['pass_at' => time()], ['apply_id' => $this->object->apply_id]);
            $this->object->is_end = 1;

            $apply = $this->object->apply;
            if ($apply->cai_wu_need == 2) {
                // 需要财务确认
                $this->object->scenario = ApprovalLog::SCENARIO_CONFIRM;
                return $this->object->save();

            } else {
                // 申请完成
                $this->object->scenario = ApprovalLog::SCENARIO_COMPLETE;
                return $this->object->save();
            }
        }
    }
}