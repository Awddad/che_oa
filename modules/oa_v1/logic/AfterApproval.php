<?php
namespace app\modules\oa_v1\logic;

use app\models\Apply;
use app\models\ApprovalLog;
use app\models\Employee;
use yii\base\Event;
use app\models\EmployeeType;

/**
 * 审批后处理
 *
 * Class AfterApproval
 * @package app\modules\oa_v1\logic
 */
class AfterApproval extends BaseLogic
{
    /**
     * 审批完成后
     * 
     * @param Event $event
     * @return boolean
     */
    public function handler($event)
    {
        if(empty($this->typeMethod[$event->data])) {
            return true;
        }
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
        if(strtotime($apply->applyPositive->positive_time) <= time()){//转正生效时间比现在小
            /**
             * @var $employee Employee
             */
            $employee = Employee::find()->where(['person_id'=>$apply->person_id])->one();
            $employee->employee_type = EmployeeType::findOne(['slug'=>'zhengshi'])->id;
            if ($employee->save()) {
                return true;
            }
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
        /**
         * @var $employee Employee
         */
        $employee = Employee::find()->where(['person_id'=>$apply->person_id])->one();
        
        $employee->org_id = $transfer->target_org_id;
        $employee->profession = $transfer->target_profession_id;
        if ($employee->save()) {
            // 权限系统接口
            EmployeeLogic::instance()->editQxEmp($employee);
            return true;
        }
        return false;
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
        /**
         * @var $employee Employee
         */
        $employee = Employee::find()->where(['person_id'=>$apply->person_id])->one();
        
        $employee->status = 3;
        $employee->leave_time = date('Y-m-d');
        if ($employee->save()) {
            //权限系统接口
            EmployeeLogic::instance()->delQxEmp($employee);
            return true;
        }
        return false;
    }
}

