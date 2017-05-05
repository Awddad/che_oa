<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/4
 * Time: 16:17
 */

namespace app\modules\oa_v1\logic;


use app\models\DdType;
use yii\helpers\ArrayHelper;

class TypeLogic extends Logic
{
    /**
     * 获取报销类型名称
     *
     * @param $typeId
     * @return mixed
     */
    public function getTypeName($typeId)
    {
        $allType = $this->getAll();
        return $allType[$typeId];
    }

    /**
     * 得到所有的报销类型
     *
     * @return array|mixed
     */
    public function getAll()
    {
        $cache = \Yii::$app->cache;
        if(!$typeArr = $cache->get('DD_TYPE')) {
            $typeArr = ArrayHelper::map(DdType::find()->asArray()->all(),'id', 'name');
            $cache->set('DD_TYPE', json_encode($typeArr), 86400);
        } else {
            $typeArr = json_decode($typeArr);
        }
        return $typeArr;
    }
}