<?php
namespace app\modules\oa_v1\logic;

use yii;
use app\models\Region;

class RegionLogic extends BaseLogic
{
	protected $key = 'oa_region';
	
	/**
	 * 地区列表
	 * @return array
	 */
    public function getRegion()
    {
        $cache = yii::$app->cache;
        if(!$region = $cache->get($this->key)){
            $region = $this->getRegionByParent();
            $cache->set($this->key, $region,86400);
        }
        return $region;
    }
    
    /**
     * 通过parent_id递归获得地区
     * @param number $parent_id
     * @return array
     */
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
    
    /**
     * 通过child获得地区字符串
     * @param int $id
     * @return string
     */
    public function getRegionByChild($id)
    {
    	$str = '';
    	$res = Region::findOne($id);
    	if($res->parent_id > 100000){
    		$str = $this->getRegionStr($res->parent_id).'-'.$res->fullName;
    	}else{
    		$str = $res->fullName;
    	}
    	return $str;
    }
}