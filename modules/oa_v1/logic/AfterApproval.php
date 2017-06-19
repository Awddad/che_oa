<?php
namespace app\modules\oa_v1\logic;

use app\models\Apply;
use app\models\Employee;
use app\logic\server\QuanXianServer;

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
        8 => '固定资产零用',
        9 => '固定资产归还',
        10 => 'Positive',
        11 => '离职',
        12 => 'Transfer',
        13 => ''
    ];

    /**
     * 审批完成后
     * 
     * @param obj $event            
     */
    public function handler($event)
    {
        $fuc = "{$this->typeMethod[$event->data]}";
        /**
         * app\models\ApprovalLog
         */
        $obj = $event->sender;
        
        //var_dump($obj->getScenario(), $obj::SCENARIO_COMPLETE);die();
        if ($obj->getScenario() == $obj::SCENARIO_COMPLETE && method_exists($this, $fuc)) {
            return $this->$fuc($obj);
        }
        return true;
    }

    /**
     * 转正
     * 
     * @param app\models\ApprovalLog $approvalLog            
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
     * @param app\models\ApprovalLog $approvalLog            
     */
    protected function Transfer($approvalLog)
    {
        $apply = Apply::findOne($approvalLog->apply_id);
        $transfer = $apply->applyTransfer;
        
        $employee = Employee::find()->where(['person_id'=>$apply->person_id])->one();
        
        $employee->org_id = $transfer->target_org_id;
        $employee->profession = $transfer->target_profession_id;
        if ($employee->save()) {
            // 权限系统接口
            $objQx = new QuanXianServer();
        }
        return true;
    }
}

