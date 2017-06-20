<?php
namespace app\modules\oa_v1\logic;

use yii;
use app\models\Region;

class RegionLogic extends BaseLogic
{
    public function getRegion()
    {
        $cache = yii::$app->cache;
        $key = 'oa_region';
        if(!$region = $cache->get($key)){
            $region = $this->getRegionByParent();
            $cache->set($key, $region);
        }
        return $region;
    }
    
    public function getRegionByParent($parent_id=100000)
    {
        $res = Region::find()->where(['parent_id'=>$parent_id])->all();
        $data = [];
        foreach($res as $v){
            $tmp = [
                'label' => $v->fullName,
                'value' => $v->id,
            ];
            $tmp_child = $this->getRegionByParent($v->id);
            $tmp_child && $tmp['children'] = $tmp_child;
            $data[] = $tmp;
        }
        return $data;
    }
}