<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/27
 * Time: 18:07
 */

namespace app\modules\oa_v1\models;


use app\models\AssetList;

/**
 * Class AssetListForm
 * @package app\modules\oa_v1\models
 */
class AssetListForm extends AssetList
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
            
        ];
    }
}