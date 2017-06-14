<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/13
 * Time: 17:54
 */

namespace app\modules\oa_v1\logic;

use app\logic\Logic;
use app\models\AssetType;
use app\models\AssetBrand;

/**
 * 基础数据
 *
 * Class AssetLogic
 * @package app\modules\oa_v1\logic
 */
class AssetLogic extends Logic
{
    /**
     * 获取类别
     *
     * @param int $assetTypeId
     * @return string
     */
    public function getAssetType($assetTypeId=1)
    {
    	$data = AssetType::find()->where(['id'=>$assetTypeId])->one();
    	if(isset($data->parent_id) && $data->parent_id > 0){
    		$parent = AssetType::find()->where(['id'=>$data->parent_id])->one();
    	}
    	
        return (isset($parent->name)?$parent->name.'-':'').$data->name;//'固定资产-电子产品-手机';
    }
    /**
     * 获得类别树
     * 
     * @param int $parent_id
     * @param array $data
     */
    public function getAssetTypeByParentId($parent_id = 0, $data=[])
    {
    	$res = AssetType::find()->where(['parent_id' => $parent_id])->orderBy(['id' => SORT_ASC])->all();
    	if(empty($res)){
    		return [];
    	}
    	foreach($res as $v){
    		if($child = $this->getAssetTypeByParentId($v->id)){
    			$data[] = [
    					'label' => $v->name,
    					'value' => $v->id,
    					'children' => $child
    			];
    		}else{
    			$data[] = [
    					'label' => $v->name,
    					'value' => $v->id,
    			];
    		}
    	}
    	return $data;
    }
    
    /**
     * 获取品牌
     *
     * @param int $assetBrand
     * @return string
     */
    public function getAssetBrand($assetBrand)
    {
    	$res = AssetBrand::find()->where(['id'=>$assetBrand])->one();
        return $res->name;
    }
    /**
     * 获得品牌列表
     */
    public function getAssetBrandList()
    {
    	$res = AssetBrand::find()->all();
    	if(empty($res)){
    		return [];
    	}
    	$data = [];
    	foreach($res as $v){
    		$data[] = [
    				'label' => $v->name,
    				'value' => $v->id,
    		];
    	}
    	return $data;
    }
}