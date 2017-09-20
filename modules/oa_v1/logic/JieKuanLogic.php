<?php

namespace app\modules\oa_v1\logic;

use app\logic\Logic;
use app\models\Apply;
use yii;

/**
 * 在借款列表
 *
 * Class JieKuanLogic
 * @package app\modules\oa_v1\logic
 */
class JieKuanLogic extends Logic
{

    /**
     * 获取员工的借款申请表ID,通过员工id
     * @param $personId
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getApplyIdsByPersonId($personId)
    {
        return Apply::find()
            ->select('apply_id')
            ->where(['person_id' => $personId, 'type' => Apply::TYPE_JIE_KUAN])
            ->all();
    }
    
    public function getHistory($personId)
    {
        $status_arr = [
            //1 => '',
            99 => '未归还',
            100 => '未归还',
            101 => '已归还',
            102 => '未归还',
        ];
        $res = Apply::find()->where(['person_id'=>$personId,'type'=>2])->all();
        $data = [];
        /**
         * @var $res Apply
         */
        foreach($res as $k => $v){
            if($v->loan->status == 1){
                continue;
            }
            $data[] = [
                'id' => $k+1,
                'time' => date('Y-m-d H:i',$v->create_time),
                'des' => $v->loan->des,
                'price' => yii::$app->formatter->asCurrency($v->loan->money),
                'status' => $status_arr[$v->loan->status],
            ];
        }
        return $data;
    }
}