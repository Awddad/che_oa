<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/20
 * Time: 09:47
 */

namespace app\modules\oa_v1\controllers;


use app\modules\oa_v1\logic\AssetLogic;
use Yii;

/**
 * 资产领取，归还
 *
 * Class AssetController
 * @package app\modules\oa_v1\controllers
 */
class AssetController extends BaseController
{
    /**
     * 固定资产领取
     */
    public function actionGet()
    {
        
    }
    
    /**
     * 固定资产归还
     */
    public function actionBack()
    {
        
    }
    
    /**
     * 可领用资产明细
     */
    public function actionCanGetList()
    {
        $data = AssetLogic::instance()->getCanGetAssetList();
        return $this->_return($data);
    }
}