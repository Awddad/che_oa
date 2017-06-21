<?php
namespace app\modules\oa_v1\logic;

use app\models\Apply;
use app\models\ApprovalLog;
use app\models\Employee;
use app\logic\server\QuanXianServer;
use yii\base\Event;

/**
 * 审批后处理
 *
 * Class AfterApproval
 * @package app\modules\oa_v1\logic
 */
class AfterApproval extends BaseLogic
{

    protected $typeMethod = [
        1 => 'Baoxiao',
        2 => 'Loan',
        3 => 'PayBack',
        4 => 'Pay',
        5 => 'Buy',
        6 => 'Demand',
        7 => 'UseChapter',
        8 => 'AssetGet',
        9 => 'AssetBack',
        10 => 'Positive',
        11 => 'Leave',
        12 => 'Transfer',
        13 => 'Open'
    ];

    /**
     * 审批完成后
     * 
     * @param Event $event
     * @return boolean
     */
    public function handler($event)
    {
        $fuc = "{$this->typeMethod[$event->data]}";
        /**
         * app\models\ApprovalLog
         */
        $obj = $event->sender;
        
        if (!$obj->hasErrors() && $obj->getScenario() == $obj::SCENARIO_COMPLETE && method_exists($this, $fuc)) {
            return $this->$fuc($obj);
        }
        return true;
    }

    /**
     * 转正
     * 
     * @param ApprovalLog $approvalLog
     * @return boolean
     */
    protected function Positive($approvalLog)
    {
        $apply = Apply::findOne($approvalLog->apply_id);
        $employee = Employee::find()->where(['person_id'=>$apply->person_id])->one();
        $employee->status = $employee->status == 1 ? 2 : $employee->status;
        if ($employee->save()) {
            return true;
        }
        return false;
    }

    /**
     * 调职
     * 
     * @param ApprovalLog $approvalLog
     * @return boolean
     */
    protected function Transfer($approvalLog)
    {
        $apply = Apply::findOne($approvalLog->apply_id);
        $transfer = $apply->applyTransfer;
        
        $employee = Employee::find()->where(['person_id'=>$apply->person_id])->one();
        
        $employee->org_id = $transfer->target_org_id;
        $employee->profession = $transfer->target_profession_id;
        if ($employee->save()) {
            // 权限系统接口（待开发）
            $objQx = new QuanXianServer();
        }
        return true;
    }
    
    /**
     * 离职
     *
     * @param ApprovalLog $approvalLog
     * @return boolean
     */
    protected function Leave($approvalLog)
    {
        $apply = Apply::findOne($approvalLog->apply_id);
        $leave = $apply->applyLeave;
        
        $employee = Employee::find()->where(['person_id'=>$apply->person_id])->one();
        
        $employee->status = 3;
        if ($employee->save()) {
            
            //权限系统接口（待开发）
            $objQx = new QuanXianServer();
            
            return true;
        }
        return false;
    }
}

