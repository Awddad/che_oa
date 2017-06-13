<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/13
 * Time: 17:54
 */

namespace app\modules\oa_v1\logic;

use app\logic\Logic;

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
    public function getAssetType($assetTypeId)
    {
        return '固定资产-电子产品-手机';
    }
    
    /**
     * 获取品牌
     *
     * @param int $assetBrand
     * @return string
     */
    public function getAssetBrand($assetBrand)
    {
        return '苹果';
    }
}