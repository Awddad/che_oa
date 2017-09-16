<?php
/**
 * Created by PhpStorm.
 * User: YJR
 * Date: 2017/9/16
 * Time: 12:15
 */

namespace app\modules\oa_v1\logic;


use app\logic\server\PhoneLetter;
use app\models\Person;

class GoodsUpApprovalLogic extends BaseLogic
{
    public function handler($event)
    {
        /**
         * @var $obj \app\models\ApprovalLog
         */
        $obj = $event->sender;
        $apply = $obj->apply;
        if($apply->type == 14) {
            switch ($obj->scenario) {
                case $obj::SCENARIO_FAIL;
                    $content = "您好，{$apply->person}，您提交了一条商品上架审批，被{$obj->approval_person}驳回 ，请及时处理。";
                    $tel = Person::findOne($apply->person_id)->phone;
                    break;
                case $obj::SCENARIO_PASS;
                    $nextApproval = $obj->nextApprovalLog;
                    $content = "您好，{$nextApproval->approval_person}，{$apply->person}提交了一条商品上架审批，请及时处理。   ";
                    $tel = Person::findOne($nextApproval->approval_person_id)->phone;
                    break;
                case $obj::SCENARIO_COMPLETE;
                    $content = "您好，{$apply->person}，您提交了一条商品上架审批已通过。";
                    $tel = Person::findOne($apply->person_id)->phone;
                    break;
                default:
                    return false;
            }
            if ($content && $tel) {
                PhoneLetter::instance()->sendYY($tel, $content);
                return true;
            }
        }
        return false;
    }
}