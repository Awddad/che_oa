<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/20
 * Time: 09:47
 */

namespace app\modules\oa_v1\controllers;

use app\models\Apply;
use app\models\Asset;
use app\models\AssetGetList;
use app\models\AssetList;
use app\models\AssetListLog;
use app\models\Person;
use app\modules\oa_v1\logic\BaseLogic;
use app\modules\oa_v1\logic\PersonLogic;
use Yii;
use app\modules\oa_v1\logic\AssetLogic;
use app\modules\oa_v1\models\AssetBackForm;
use app\modules\oa_v1\models\AssetGetForm;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

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
            'can-back-list' => ['get'],
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
    public function actionCanGetAsset()
    {
        $param = \Yii::$app->request->get();
        
        $data = AssetLogic::instance()->getCanGetAsset($param);
        return $this->_return($data);
    }
    
    /**
     * 待归还资产
     * @return array
     */
    public function actionCanBackAsset()
    {
        $personId = $this->arrPersonInfo['person_id'];
        $data = AssetLogic::instance()->getCanBackAsset($personId);
        return $this->_return($data);
    }
    
    /**
     * 固定资产库存列表
     *
     * @return array
     */
    public function actionList()
    {
        $param = Yii::$app->request->get();
        $query = Asset::find()->where([]);
    
        $keyword = ArrayHelper::getValue($param, 'keyword');
        if($keyword) {
            $query->andWhere([
                'like','name', $keyword
            ]);
        }
        $time = ArrayHelper::getValue($param, 'time');;
        if (!empty($time) && strlen($time > 20)) {
            $beforeTime = strtotime(substr($time, 0, 10));
            $afterTime = strtotime('+1day', strtotime(substr($time, -10)));
            $query->andWhere(['between', 'create_time', $beforeTime, $afterTime]);
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
        /**
         * @var Asset $v
         */
        foreach ($model as $k => $v) {
            $data[] = [
                'index' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                'id' => $v->id,
                'asset_type_name' => $v->asset_type_name,
                'asset_brand_name' => $v->asset_brand_name,
                'name' => $v->name,
                'amount' => $v->amount,
                'price' => Yii::$app->formatter->asCurrency($v->price)
            ];
        }
  
        return $this->_return([
            'list' => $data,
            'pages' => BaseLogic::instance()->pageFix($pagination)
        ]);
    }
    
    /**
     * 资产列表详情
     *
     * @param $asset_id
     * @return array
     */
    public function actionAssetList($asset_id)
    {
        $param = Yii::$app->request->get();
        $query = AssetList::find()->where(['asset_id' => $asset_id]);
    
        $keyword = ArrayHelper::getValue($param, 'keyword');
        if($keyword) {
            $query->andWhere([
                'like','name', $keyword
            ]);
        }
        $time = ArrayHelper::getValue($param, 'time');;
        if (!empty($time) && strlen($time > 20)) {
            $beforeTime = strtotime(substr($time, 0, 10));
            $afterTime = strtotime('+1day', strtotime(substr($time, -10)));
            $query->andWhere(['between', 'create_time', $beforeTime, $afterTime]);
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
        /**
         * @var AssetList $v
         */
        foreach ($model as $k => $v) {
            $data[$k] = [
                'index' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                'id' => $v->id,
                'created_at' => date("Y-m-d H:i", $v->created_at),
                'stock_number' => $v->stock_number,
                'asset_number' => $v->asset_number,
                'status' => $v::STATUS[$v->status],
                'price' => Yii::$app->formatter->asCurrency($v->price)
            ];
            if($v->status == 2) {
                $assetGetList = AssetGetList::findOne([
                    'asset_list_id' => $v->id,
                    'status' => 1
                ]);
                $person = Person::findOne($assetGetList->person_id);
                $org = PersonLogic::instance()->getOrgName($person);
                $data[$k]['use_person'] = $person->person_name;
                $data[$k]['org'] = implode('-', $org);
                $data[$k]['use_day'] = ceil((time() - $assetGetList->created_at)/ 86400);
            }
        }
        return $this->_return([
            'list' => $data,
            'pages' => BaseLogic::instance()->pageFix($pagination)
        ]);
    }
    
    /**
     * 资产使用详情
     *
     * @param $asset_list_id
     *
     * @return array
     */
    public function actionAssetDetail($asset_list_id)
    {
        $assetList = AssetList::findOne($asset_list_id);
        
        $data = [
            'asset_type_name' => $assetList->asset->asset_type_name,
            'asset_brand_name' => $assetList->asset->asset_brand_name,
            'name' => $assetList->asset->name,
            'asset_number' => $assetList->asset_number,
            'stock_number' => $assetList->stock_number,
            'sn_number' => $assetList->sn_number,
            'price' => $assetList->price,
            'status' => $assetList->status,
        ];
        
        return $this->_return($data);
    }
    
    public function actionAssetListLog($asset_list_log_id)
    {
        $param = Yii::$app->request->get();
        $query = AssetListLog::find()->where(['asset_id' => $asset_list_log_id]);
    
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
        /**
         * @var AssetListLog $v
         */
        foreach ($model as $k => $v) {
            $person = Person::findOne($v->person_id);
            $org = PersonLogic::instance()->getOrgName($person);
            $data[$k] = [
                'index' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                'id' => $v->id,
                'created_at' => date("Y-m-d H:i", $v->created_at),
                'person_name' => $person->person_name,
                'org' => implode('-', $org),
                'type' => $v->type,
                'des' => $v->des
            ];
        }
        return $this->_return([
            'list' => $data,
            'pages' => BaseLogic::instance()->pageFix($pagination)
        ]);
    }
    
}