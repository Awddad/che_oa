<?php

namespace app\modules\oa_v1\logic;

use app\logic\Logic;
use app\models\Apply;

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
}