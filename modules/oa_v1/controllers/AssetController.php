<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/20
 * Time: 09:47
 */

namespace app\modules\oa_v1\controllers;

use app\models\ApplyDemand;
use app\models\Asset;
use app\models\AssetList;
use app\models\AssetListLog;
use app\models\Person;
use app\modules\oa_v1\logic\BaseLogic;
use app\modules\oa_v1\models\AssetListForm;
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
            return $this->_returnError(4400, null, BaseLogic::instance()->getFirstError($model->errors));
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
            return $this->_returnError(4400, null, BaseLogic::instance()->getFirstError($model->errors));
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
    
        $keyword = ArrayHelper::getValue($param, 'keywords');
        if($keyword) {
            $query->andWhere([
                'like','name', $keyword
            ]);
        }
        $beforeTime = strtotime(ArrayHelper::getValue($param, 'start_time'));
        $afterTime = strtotime(ArrayHelper::getValue($param, 'end_time'));
        if ($beforeTime && $afterTime) {
            $afterTime = strtotime('+1day', $afterTime);
            $query->andWhere([
                'and',
                ['>', 'create_time', $beforeTime],
                ['<', 'create_time', $afterTime]
            ]);
        }
    
        $pageSize = ArrayHelper::getValue($param, 'page_size', 20);
    
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
                'price' => Yii::$app->formatter->asCurrency($v->price),
            ];
        }
  
        return $this->_return([
            'list' => $data,
            'page' => BaseLogic::instance()->pageFix($pagination)
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
        $query = AssetList::find()->innerJoin('oa_asset', 'oa_asset.id = oa_asset_list.asset_id')->where([
            'oa_asset_list.asset_id' => $asset_id
        ]);
    
        $status = ArrayHelper::getValue($param, 'status');
        if ($status) {
            $query->andWhere([
                'oa_asset_list.status' => $status
            ]);
        }
    
        $beforeTime = strtotime(ArrayHelper::getValue($param, 'start_time'));
        $afterTime = strtotime(ArrayHelper::getValue($param, 'end_time'));
        if ($beforeTime && $afterTime) {
            $afterTime = strtotime('+1day', $afterTime);
            $query->andWhere([
                'and',
                ['>', 'oa_asset_list.created_at', $beforeTime],
                ['<', 'oa_asset_list.created_at', $afterTime]
            ]);
        }
    
        $keyword = ArrayHelper::getValue($param, 'keywords');
        if($keyword) {
            $person = Person::find()->filterWhere(['like', 'person_name',$keyword ])->all();
            if(!empty($person)){
                $personIds = ArrayHelper::getColumn($person, 'person_id');
                $query->andWhere([
                    'or',
                    ['like','oa_asset.name', $keyword],
                    ['like','oa_asset_list.asset_number', $keyword],
                    ['in','oa_asset_list.person_id', $personIds],
                ]);
            } else {
                $query->andWhere([
                    'or',
                    ['like','oa_asset.name', $keyword],
                    ['like','oa_asset_list.asset_number', $keyword],
                ]);
            }
    
        }
    
        $pageSize = ArrayHelper::getValue($param, 'page_size', 20);
    
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
            if($v->status == 2 && $v->person_id) {
                $person = Person::findOne($v->person_id);
                $usePerson = $person->person_name;
                $org = $person->org_full_name;
                $use = AssetListLog::find()->where([
                    'asset_list_id' => $v->id,
                    'person_id' => $v->person_id,
                    'type' => 2,
                ])->one();
                if ($use) {
                    $useDay = round((time()-$use->created_at)/3600/24).'天';
                } else {
                    $useDay = '--';
                }
                
            } else {
                $usePerson = '--';
                $org = '--';
                $useDay = '--';
            }
          
            $status = $v::STATUS[$v->status];
            
            $data[$k] = [
                'index' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                'id' => $v->id,
                'created_at' => date("Y-m-d H:i", $v->created_at),
                'stock_number' => $v->stock_number,
                'asset_number' => $v->asset_number,
                'status' => $status,
                'price' => Yii::$app->formatter->asCurrency($v->price),
                'use_person' => $usePerson,
                'org' => $org,
                'use_day' => $useDay
            ];
        }
        return $this->_return([
            'list' => $data,
            'page' => BaseLogic::instance()->pageFix($pagination)
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
        if ($assetList->apply_buy_id) {
            /**
             * @var ApplyDemand $demand
             */
            $demand = ApplyDemand::find()->where(['apply_buy_id' => $assetList->apply_buy_id])->one();
            if ($demand) {
                $demandId = $demand->apply_id;
            } else {
                $demandId = '--';
            }
        } else {
            $demandId = '--';
        }
        $data = [
            'asset_type_name' => $assetList->asset->asset_type_name,
            'asset_brand_name' => $assetList->asset->asset_brand_name,
            'name' => $assetList->asset->name,
            'asset_number' => $assetList->asset_number,
            'stock_number' => $assetList->stock_number,
            'sn_number' => $assetList->sn_number,
            'price' => $assetList->price,
            'status' => $assetList->status,
            'status_name' => $assetList::STATUS[$assetList->status],
            'apply_buy_id' => $assetList->apply_buy_id,
            'apply_demand_id' => $demandId,
            'org' => '',
            'use_person' => ''
        ];
        if($assetList->status == 2 && $assetList->person_id) {
            $person = Person::findOne($assetList->person_id);
            $data['use_person'] = $person->person_name;
            $data['org'] = $person->org_full_name;
            $use = AssetListLog::find()->where([
                'asset_list_id' => $assetList->id,
                'person_id' => $assetList->person_id,
                'type' => 2,
            ])->one();
            if ($use) {
                $useDay = round((time() - $use->created_at) / 3600 / 24);
            } else {
                $useDay = 0;
            }
            $data['use_day'] = '已使用'.$useDay.'天';
        }
        
        return $this->_return($data);
    }
    
    /**
     * 资产使用轨迹
     *
     * @param $asset_list_id
     *
     * @return array
     */
    public function actionAssetListLog($asset_list_id)
    {
        $param = Yii::$app->request->get();
        $query = AssetListLog::find()->where(['asset_list_id' => $asset_list_id]);
    
        $pageSize = ArrayHelper::getValue($param, 'page_size', 20);
    
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
            $org = $person->org_full_name;
            $data[$k] = [
                'index' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                'id' => $v->id,
                'created_at' => date("Y-m-d H:i", $v->created_at),
                'person_name' => $person->person_name,
                'org' => $org,
                'type' => $v::TYPE[$v->type],
                'des' => $v->des
            ];
        }
        return $this->_return([
            'list' => $data,
            'page' => BaseLogic::instance()->pageFix($pagination)
        ]);
    }
    
    /**
     * 丢失／报废
     *
     * @return array
     */
    public function actionUpdateAssetStock()
    {
        $param = Yii::$app->request->post();
         
        if(empty($param) || !isset($param['asset_list_id']) || !isset($param['status']) || !isset($param['des'])) {
             $this->_returnError(400);
        }
        
        $rst= AssetLogic::instance()->updateAssetList($param, $this->arrPersonInfo);
        if($rst) {
            return $this->_return([]);
        }
        return $this->_returnError(500);
    }
    
    /**
     * 添加 sn_number
     *
     * @return array
     */
    public function actionSnNumber()
    {
        $param = Yii::$app->request->post();
    
        if(empty($param) || !isset($param['asset_list_id'])  || !isset($param['sn_number'])) {
            $this->_returnError(400);
        }
        $rst = AssetList::updateAll(['sn_number' => $param['sn_number']], ['id' => $param['asset_list_id']]);
        if($rst) {
            return $this->_return([], 200, '添加成功');
        }
        return $this->_returnError(500);
    }
    
    /**
     * 新增库存
     *
     * @return array
     */
    public function actionAddAsset()
    {
        $model = new AssetListForm();
    
        $param = \Yii::$app->request->post();
        $data['AssetListForm'] = $param;
        if ($model->load($data) && $model->validate() &&  $model->save($this->arrPersonInfo)) {
            return $this->_return([]);
        } else {
            return $this->_returnError(4400, null, BaseLogic::instance()->getFirstError($model->errors));
        }
    }
}