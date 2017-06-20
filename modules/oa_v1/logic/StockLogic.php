<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/19
 * Time: 16:50
 */

namespace app\modules\oa_v1\logic;


use app\logic\Logic;
use app\models\Stock;


/**
 * 资产明细
 *
 * Class StockLogic
 * @package app\modules\oa_v1\logic
 */
class StockLogic extends Logic
{
    public function getStock()
    {
        $socket = Stock::find()->where([
            '!=', 'free_amount', 0
        ])->all();
        $data = [];
        if(empty($socket)) {
            return $data;
        }
        /**
         * @var Stock $v
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
}