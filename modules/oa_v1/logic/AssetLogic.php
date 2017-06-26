<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/13
 * Time: 17:54
 */

namespace app\modules\oa_v1\logic;

use app\logic\Logic;
use app\models\Apply;
use app\models\Asset;
use app\models\AssetBack;
use app\models\AssetGetList;
use app\models\AssetList;
use app\models\AssetListLog;
use app\models\AssetType;
use app\models\AssetBrand;
use app\models\Person;
use yii\data\Pagination;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

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
     *
     * @param array $param
     * @return array
     */
    public function getCanGetAsset($param)
    {
        $query = Asset::find()->where([
            '>', 'free_amount', 0
        ]);
        if(isset($param['keyword'])) {
            $query->andWhere([
                'or',
                ['like', 'asset_type_name', $param['keyword']],
                ['like', 'name', $param['keyword']],
            ]);
        }
        $pageSize = ArrayHelper::getValue($param, 'pageSize', 20);
        $pagination = new Pagination([
            'defaultPageSize' => $pageSize,
            'totalCount' => $query->count(),
        ]);
        $model = $query->orderBy(["id" => SORT_DESC])
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        $data = [];
        if(empty($model)) {
            return $data;
        }
        /**
         * @var Asset $v
         */
        foreach ($model as $v){
            $data[] = [
                'id' => $v->id,
                'asset_type' => $v->asset_type_name,
                'asset_brand' => $v->asset_brand_name,
                'name' => $v->name,
                'price' => $v->price
            ];
        }
        
        return [
            'list' => $data,
            'page' => BaseLogic::instance()->pageFix($pagination)
        ];
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
                    'id' => $v->id,
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
            ->where(['person_id'=>$person_id])
            ->andWhere(['in', 'status', [5, 4, 2]])
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
    
    /**
     * 固定资产领取审批通过操作
     *
     * @param Apply $apply
     *
     * @return boolean
     * @throws Exception
     */
    public function assetGetComplete($apply)
    {
        $assetGetList = AssetGetList::find()->where(['apply_id' => $apply->apply_id])->all();
        /**
         * @var AssetGetList $v
         */
        foreach ($assetGetList as $v) {
            /**
             * @var AssetList $assetList
             */
            $assetList = AssetList::find()->where([
                'asset_id' => $v->asset_id,
                'status' => 1
            ])->orderBy(['id' => SORT_ASC])->one();
            $v->asset_list_id = $assetList->id;
            $v->status = 2;
            if (!$v->save()) {
                throw new Exception('资产分配失败');
            }
            $this->addAssetListLog($v->person_id, $assetList->id, $apply->apply_id);
        }
        return false;
    }
    
    /**
     * 固定资产领取审批不通过或者用户撤销操作,状态变为失败
     *
     * @param Apply $apply
     *
     * @return boolean
     */
    public function assetGetCancel($apply)
    {
        return AssetGetList::updateAll(['status' => 3],['in', 'id', $apply->apply_id]);
    }
    
    /**
     * 资产归还
     *
     * @param Apply $apply
     * @return boolean
     * @throws Exception
     */
    public function assetBackComplete($apply)
    {
        $assetBack = AssetBack::findOne($apply->apply_id);
        $assetGetList = AssetGetList::find()->where(['in', 'id', explode(',', $assetBack->asset_list_ids)])->all();
        /**
         * @var AssetGetList $v
         */
        foreach ($assetGetList as $k => $v) {
            $v->status = 5;
            if ($v->save()) {
                $this->addAssetListLog($v->person_id, $v->asset_list_id, $apply->apply_id);
            } else {
                throw new Exception('资产归还失败！');
            }
        }
        return true;
    }
    
    /**
     * 资产归还，取消或者撤销
     * @param $apply
     * @return int
     */
    public function assetBackCancel($apply)
    {
        return AssetGetList::updateAll(['status' => 2],['in', 'id', $apply->apply_id]);
    }
    
    
    /**
     * 资产使用日志
     *
     * @param int $personId
     * @param int $assetListId
     * @param int $applyId
     *
     * @return boolean
     * @throws \yii\base\Exception
     */
    public function addAssetListLog($personId, $assetListId, $applyId)
    {
        $log = new AssetListLog();
        $log->person_id = $personId;
        $log->asset_list_id = $assetListId;
        $log->type = 2;
        $log->des = '领用,审批单号：'.$applyId;
        $log->created_at = time();
        if ($log->save()) {
            return true;
        }
        throw new \yii\base\Exception('日志保存时报');
    }
    
}