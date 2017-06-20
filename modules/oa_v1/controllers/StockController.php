<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/19
 * Time: 16:48
 */

namespace app\modules\oa_v1\controllers;


use app\modules\oa_v1\logic\StockLogic;


/**
 * 库存相关
 * Class StockController
 * @package app\modules\oa_v1\controllers
 */
class StockController extends BaseController
{
    /**
     * 资产明细
     */
    public function actionList()
    {
        $data = StockLogic::instance()->getStock();
        return $this->_return($data);
    }
}