<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/8/11
 * Time: 16:48
 */

namespace app\modules\oa_v1\controllers;

use app\modules\oa_v1\logic\GoodsUpLogic;
use yii\helpers\ArrayHelper;

/**
 * 商品上架
 *
 * Class GoodsUpController
 * @package app\modules\oa_v1\controllers
 */
class GoodsUpController extends BaseController
{
    /**
     * 品牌
     *
     * @return array
     */
    public function actionBrand()
    {
        $data = GoodsUpLogic::instance()->brand();
        if (!$data) {
            $this->_returnError('4400',  null, '为获取到数据，请稍后再试！');
        }
        return $this->_return($data);
        
    }
    
    /**
     * 厂商
     *
     * @param $brand_id
     *
     * @return array
     */
    public function actionFactory($brand_id)
    {
        $data = GoodsUpLogic::instance()->factory($brand_id);
        if (!$data) {
            $this->_returnError('4400',  null, '为获取到数据，请稍后再试！');
        }
        return $this->_return($data);
    }
    
    /**
     * 车系
     *
     * @param $brand_id
     * @param $factory_id
     *
     * @return array
     */
    public function actionSeries($brand_id, $factory_id)
    {
        $data = GoodsUpLogic::instance()->series($brand_id,$factory_id);
        if (!$data) {
            $this->_returnError('4400',  null, '为获取到数据，请稍后再试！');
        }
        return $this->_return($data);
    }
    
    
    
    /**
     * 车型
     *
     * @param $series_id
     *
     * @return array
     */
    public function actionCars($series_id)
    {
        $data = GoodsUpLogic::instance()->car($series_id);
        if (!$data) {
            $this->_returnError('4400',  null, '为获取到数据，请稍后再试！');
        }
        return $this->_return($data);
    }
    
    
    /**
     * 颜色
     *
     * @param $car_id
     *
     * @return array
     */
    public function actionColor($car_id)
    {
        $data = GoodsUpLogic::instance()->colors($car_id);
        if (!$data) {
            $this->_returnError('4400',  null, '为获取到数据，请稍后再试！');
        }
        return $this->_return(ArrayHelper::index($data, null,'type'));
    }
}