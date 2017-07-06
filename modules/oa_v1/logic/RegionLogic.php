<?php
namespace app\modules\oa_v1\logic;

use yii;
use app\models\Region;

class RegionLogic extends BaseLogic
{
	protected $key = 'oa_region';
	protected $key_all = 'oa_region_all';
	
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
    	$res = $this->getRegionAll();
    	$tmp = $res[$id];
    	while($tmp['parent_id'] > 0){
    	    $str = $tmp['fullName'].'-'.$str;
    	    $parent_id = $tmp['parent_id'];
    	    $tmp = $res[$parent_id];
    	    unset($parent_id);
    	}
    	return mb_substr($str,0,-1);
    }
    /**
     * 通过child获得地区id
     * @param int $id
     * @return array
     */
    public function getRegionIdByChild($id)
    {
        $data = [];
    	$res = $this->getRegionAll();
    	$tmp = $res[$id];
    	while($tmp['parent_id'] > 0){
    	    $data[] = $tmp['id'];
    	    $parent_id = $tmp['parent_id'];
    	    $tmp = $res[$parent_id];
    	    unset($parent_id);
    	}
    	return array_reverse($data);
    }
    
    protected function getRegionAll()
    {
        $cache = yii::$app->cache;
        if(!$region = $cache->get($this->key_all)){
            $tmp = Region::find()->asArray()->all();
            $region = [];
            foreach($tmp as $v){
                $region[$v['id']] = $v;
            }
            $cache->set($this->key_all, $region,86400);
        }
        return $region;
    }
}