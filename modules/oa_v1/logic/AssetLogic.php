<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/13
 * Time: 17:54
 */

namespace app\modules\oa_v1\logic;

use app\logic\Logic;
use app\models\Asset;
use app\models\AssetGetList;
use app\models\AssetList;
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
     * @return array
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
    
    /**
     * 可领用资产列表
     * @return array
     */
    public function getCanGetAsset()
    {
        $socket = Asset::find()->where([
            '!=', 'free_amount', 0
        ])->all();
        $data = [];
        if(empty($socket)) {
            return $data;
        }
        /**
         * @var Asset $v
         */
        foreach ($socket as $v){
            $data[] = [
                'asset_type' => AssetLogic::instance()->getAssetType($v->asset_type_id),
                'asset_brand' => AssetLogic::instance()->getAssetBrand($v->asset_brand_id),
                'name' => $v->name,
                'price' => $v->price
            ];
        }
        return $data;
    }
    
    /**
     * 可归还资产列表
     *
     * @param $personId
     * @return array
     */
    public function getCanBackAsset($personId)
    {
        $list = AssetGetList::find()->where([
            'status' => 2,
            'person_id' => $personId
        ])->all();
        $data = [];
        if(!empty($list)) {
            /**
             * @var AssetGetList $v
             */
            $assetLogic = AssetLogic::instance();
            foreach ($list as $k => $v) {
                $asset = Asset::findOne($v->asset_id);
                $data[] = [
                    'index' => $k + 1,
                    'asset_type' => $assetLogic->getAssetType($asset->asset_type_id),
                    'asset_brand' => $assetLogic->getAssetBrand($asset->asset_brand_id),
                    'name' => $asset->name,
                    'price' => $asset->price,
                    'socket_number' => AssetList::findOne($v->asset_list_id)->stock_number
                ];
            }
        }
        return $data;
    }
    
    public function getAssetHistory($person_id)
    {
        $status_arr = [
            5 => '已归还',
            4 => '未归还',//归还中
            //3 => '申请失败',
            2 => '未归还',//'申请通过',
            //1 => '申请中'
        ];
        $res = AssetGetList::find()
            ->where(['person_id'=>$person_id,['and',['in', 'status', [5, 4, 2]]]])
            ->orderBy(['created_at'=>SORT_ASC])
            ->all();
        
        $data = [];
        foreach($res as $v){
            $asset = Asset::findOne($v->asset_id);
            $data[] = [
                'type' => $this->getAssetType($asset->asset_type_id),//类别
                'sn' => AssetList::findOne($v->asset_list_id)->sn_number,//库存编号
                'brand' => $this->getAssetBrand($asset->asset_brand_id),//品牌
                'name' => $asset->name,//名称
                'price' => $asset->price,//价格
                'status' => $status_arr[$v->status],//状态
            ];
        }
        return $data;
    }
}