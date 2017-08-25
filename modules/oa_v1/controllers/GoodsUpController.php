<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/8/11
 * Time: 16:48
 */

namespace app\modules\oa_v1\controllers;

use app\models\GoodsUp;
use app\models\GoodsUpDetail;
use app\modules\oa_v1\logic\BaseLogic;
use app\modules\oa_v1\logic\GoodsUpLogic;
use app\modules\oa_v1\models\GoodsUpForm;
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
     * 创建商品上架申请单
     *
     * @return array
     */
    public function actionIndex()
    {
        $form = new GoodsUpForm();
        $form->load(['GoodsUpForm' => \Yii::$app->request->post()]);
        if (!$form->validate()) {
            return $this->_returnError(4400, null, BaseLogic::instance()->getFirstError($form->errors));
        }
        if ($rst = $form->save($this->arrPersonInfo)) {
            return $this->_return($rst);
        }
        return $this->_returnError(4400, null, '创建商品上架申请单失败');
    }
    
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
        if (!$car_id) {
            return $this->_returnError(403);
        }
        $data = GoodsUpLogic::instance()->colors($car_id);
        if (!$data) {
            $this->_returnError('4400',  null, '为获取到数据，请稍后再试！');
        }
        return $this->_return(ArrayHelper::index($data, null,'type'));
    }
    
    /**
     * 供应商
     * 
     * @return array
     */
    public function actionSupplier()
    {
        $goodsUp = GoodsUpDetail::find()->select([
            'supplier', //供应商
            'supplier_type', //供应商类型
            'contacts', //联系人
            'job', //职务
            'phone', //电话
            'has_bus_contracts', //是同否提供公车合同
        ])->groupBy('supplier')->asArray()->all();
        
        return $this->_return($goodsUp);
    }
}