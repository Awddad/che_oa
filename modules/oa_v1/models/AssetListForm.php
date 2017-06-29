<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/27
 * Time: 18:07
 */

namespace app\modules\oa_v1\models;


use app\models\Asset;
use app\modules\oa_v1\logic\AssetLogic;
use yii\base\Model;
use yii\db\Exception;

/**
 * 资产库存列表
 *
 * Class AssetListForm
 * @package app\modules\oa_v1\models
 */
class AssetListForm extends Model
{
    public $asset_type_id;
    
    public $asset_brand_id;
    
    public $name;
    
    public $price;
    
    public $amount;
    
    
    public function rules()
    {
        return [
            [['asset_type_id', 'asset_brand_id', 'name', 'price', 'amount'], 'required'],
            [['asset_type_id', 'asset_brand_id', 'amount'], 'integer'],
            [['name', 'amount'], 'string']
        
        ];
    }
    
    /**
     * 新增库存
     *
     * @return Asset
     * @throws Exception
     */
    public function save()
    {
        $asset = Asset::find()->where([
            'asset_type_id' => $this->asset_type_id,
            'asset_brand_id' => $this->asset_brand_id,
            'name' => $this->name,
        ])->one();
        $assetLogic = AssetLogic::instance();
        if(empty($asset)) {
            $asset = new Asset();
            $asset->asset_type_id = $this->asset_type_id;
            $asset->asset_type_name = $assetLogic->getAssetType($this->asset_type_id);
            $asset->asset_brand_id = $this->asset_brand_id;
            $asset->asset_brand_name = $assetLogic->getAssetBrand($this->asset_brand_id);
            $asset->name = $this->name;
            $asset->amount = $this->amount;
            $asset->price = $this->price;
            $asset->free_amount = $this->amount;
        } else {
            $asset->amount += $this->amount;;
            $asset->free_amount += $this->amount;
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!$asset->save()) {
                throw new Exception('入库失败');
            }
            $assetLogic->addAssetList($asset);
            $transaction->commit();
            return $asset;
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}