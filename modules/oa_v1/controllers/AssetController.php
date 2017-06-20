<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/20
 * Time: 09:47
 */

namespace app\modules\oa_v1\controllers;


use app\models\AssetBack;
use app\modules\oa_v1\logic\AssetLogic;
use app\modules\oa_v1\models\AssetBackForm;
use app\modules\oa_v1\models\AssetGetForm;
use Yii;

/**
 * 资产领取，归还
 *
 * Class AssetController
 * @package app\modules\oa_v1\controllers
 */
class AssetController extends BaseController
{
    public function verbs()
    {
        return [
            'get' => ['post'],
            'back' => ['post'],
            'can-get-list' => ['get'],
        ];
    }
    
    /**
     * 固定资产领取
     */
    public function actionGet()
    {
        $model = new AssetGetForm();
    
        $param = \Yii::$app->request->post();
        $data['AssetGetForm'] = $param;
        if ($model->load($data) && $model->validate() &&  $model->save($this->arrPersonInfo)) {
            return $this->_return($model->apply_id);
        } else {
            return $this->_return($model->errors, 400);
        }
    }
    
    /**
     * 固定资产归还
     */
    public function actionBack()
    {
        $model = new AssetBackForm();
    
        $param = \Yii::$app->request->post();
        $data['AssetBackForm'] = $param;
        if ($model->load($data) && $model->validate() &&  $model->save($this->arrPersonInfo)) {
            return $this->_return($model->apply_id);
        } else {
            return $this->_return($model->errors, 400);
        }
    }
    
    /**
     * 可领用资产明细
     */
    public function actionCanGetList()
    {
        $data = AssetLogic::instance()->getCanGetAssetList();
        return $this->_return($data);
    }
    
    public function actionCanBackList()
    {
        $personId = $this->arrPersonInfo->person_id;
        $data = AssetLogic::instance()->getCanBackList($personId);
        return $this->_return($data);
    }
}