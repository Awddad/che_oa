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
use app\modules\oa_v1\logic\AssetImportLogic;
use app\modules\oa_v1\logic\BaseLogic;
use app\modules\oa_v1\models\AssetListForm;
use moonland\phpexcel\Excel;
use Yii;
use app\modules\oa_v1\logic\AssetLogic;
use app\modules\oa_v1\models\AssetBackForm;
use app\modules\oa_v1\models\AssetGetForm;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

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
        $query = Asset::find()->where(['is_delete'=>0]);
    
        $keyword = trim(ArrayHelper::getValue($param, 'keywords'));
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
    public function actionAssetList()
    {
        $param = Yii::$app->request->get();
        $query = AssetList::find()->innerJoin('oa_asset', 'oa_asset.id = oa_asset_list.asset_id');

        $asset_id = ArrayHelper::getValue($param, 'asset_id');
        if($asset_id){
            $query->andwhere([
                'oa_asset_list.asset_id' => $asset_id
            ]);
        }

        $status = ArrayHelper::getValue($param, 'status');
        if ($status) {
            $query->andWhere([
                'oa_asset_list.status' => $status
            ]);
        }

        $asset_type_id = ArrayHelper::getValue($param, 'asset_type_id');
        if ($asset_type_id) {
            $query->andWhere([
                'oa_asset.asset_type_id' => $asset_type_id
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
    
        $keyword = trim(ArrayHelper::getValue($param, 'keywords'));
        if($keyword) {
            $person = Person::find()->filterWhere(['like', 'person_name',$keyword ])->all();
            if(!empty($person)){
                $personIds = ArrayHelper::getColumn($person, 'person_id');
                $query->andWhere([
                    'or',
                    ['like','oa_asset.name', $keyword],
                    ['like','oa_asset_list.asset_number', $keyword],
                    ['like','oa_asset_list.stock_number', $keyword],
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
        //echo $query->createCommand()->getRawSql();die();
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
                ])->andWhere([
                    'in', 'type', [2, 6]
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

            $asset = Asset::findOne($v->asset_id);
            $data[$k] = [
                'index' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                'id' => $v->id,
                'asset_type_name' => $asset->asset_type_name,
                'asset_type_id' => $asset->asset_type_id,
                'asset_brand_name' => $asset->asset_brand_name,
                'asset_brand_id' => $asset->asset_brand_id,
                'name' => $asset->name,
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
            ])->andWhere([
                'in', 'type', [2, 6]
            ])->orderBy('id desc')->one();
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
            if(!empty($person)) {
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
            } else {
                $data[$k] = [
                    'index' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                    'id' => $v->id,
                    'created_at' => date("Y-m-d H:i", $v->created_at),
                    'person_name' => '--',
                    'org' => '--',
                    'type' => $v::TYPE[$v->type],
                    'des' => $v->des
                ];
            }
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
        $model->setScenario($model::SCENARIO_ADD);
        $param = \Yii::$app->request->post();
        $data['AssetListForm'] = $param;
        if ($model->load($data) && $model->validate() &&  $model->save($this->arrPersonInfo)) {
            return $this->_return([]);
        } else {
            return $this->_returnError(4400, null, BaseLogic::instance()->getFirstError($model->errors));
        }
    }
    
    /**
     * 资产导入
     */
    public function actionImport()
    {
        $files = UploadedFile::getInstanceByName('file');
        $logic =  AssetImportLogic::instance();
        $result = $logic->import($files->tempName);
        if ($logic->errorCode != 200) {
            return $this->_returnError($logic->errorCode, null, $result);
        }
        return $this->_return($result);
    }
    
    /**
     * 固定资产分配
     *
     * @return array
     */
    public function actionAssetAllot()
    {
        $asset_list_id = Yii::$app->request->post('asset_list_id');
        $person_id = Yii::$app->request->post('person_id');
        if (!$person_id || !$asset_list_id) {
            return $this->_returnError(400);
        }
        $person = Person::findOne($person_id);
        $assetList = AssetList::findOne($asset_list_id);
        if (empty($assetList) || empty($person)) {
            return $this->_returnError(4400, null, '未找到该信息');
        }
        if ($assetList->status != 1) {
            return $this->_returnError(4400, null, '该资产不能分配');
        }
        $rst = AssetLogic::instance()->assetAllot($assetList, $person, $this->arrPersonInfo);
        if ($rst) {
            return $this->_return(null);
        }
        return $this->_returnError(4400, null, AssetLogic::instance()->error);
    }
    
    /**
     * 固定资产回收
     *
     * @return array
     */
    public function actionAssetRecover()
    {
        $asset_list_id = Yii::$app->request->post('asset_list_id');
        $des = Yii::$app->request->post('des');
        if (!$asset_list_id || !$des) {
            return $this->_returnError(400);
        }
        $assetList = AssetList::findOne($asset_list_id);
        if (empty($assetList)) {
            return $this->_returnError(4400, null, '未找到该信息');
        }
        if ($assetList->status != 2) {
            return $this->_returnError(4400, null, '该资产不能回收');
        }
        $rst = AssetLogic::instance()->assetRecover($assetList, $this->arrPersonInfo, $des);
        if ($rst) {
            return $this->_return(null);
        }
        return $this->_returnError(4400, null, AssetLogic::instance()->error);
    }
    
    public function actionExport()
    {
        $assetList = AssetList::find()->all();
        $data = [];
        /**
         * @var AssetList $v
         */
        foreach ($assetList as $v) {
            /**
             * @var Asset $asset
             */
            $asset = $v->asset;
            $typeArr = explode('-', $asset->asset_type_name);
            $use_person = $org = '';
            if ($v->status == 2 && $v->person_id && $person = Person::findOne($v->person_id)) {
                $use_person = $person->person_name;
                $org= $person->org_full_name;
            }
            $assetListLog = AssetListLog::find()->where([
                'asset_list_id' => $v->id
            ])->orderBy('id desc')->one();
            $des = (!empty($assetListLog)) ? $assetListLog->des : '';
            $data[] = [
                'type' => $typeArr[0],
                'type_detail' => isset($typeArr[1]) ? $typeArr[1] : $typeArr[0],
                'brand_name' => $asset->asset_brand_name,
                'name' => $asset->name,
                'created_at' => date('Y-m-d H:i', $v->created_at),
                'stock_number' => $v->stock_number,
                'asset_number' => $v->asset_number,
                'sn_number' => $v->sn_number,
                'status' => AssetList::STATUS[$v->status],
                'use_person' => $use_person,
                'org' => $org,
                'price' => $v->price,
                'des' => $des,
            ];
        }
        $header = [
            'type' => '类别',
            'type_detail' => '类别详情',
            'brand_name' => '品牌',
            'name' => '名称',
            'created_at' => '入库时间',
            'stock_number' => '库存编号',
            'asset_number' => '资产编号',
            'sn_number' => 'sn号',
            'status' => '状态',
            'use_person' => '使用人',
            'org' => '部门',
            'price' => '采购价',
            'des' => '说明'
        ];
        $columns = array_keys($header);
        Excel::export([
            'models' => $data,
            'columns' => $columns,
            'headers' => $header,
            'fileName' => '固定资产库存管理',
        ]);
    }
    
    /**
     * 搜索员工资产
     *
     * @return array
     */
    public function actionGetPersonAsset()
    {
        $personId = Yii::$app->request->get('person_id');
        $query = AssetList::find()->where(['status' => 2]);
        if($personId) {
            $query->andWhere(['person_id' => $personId,]);
        }
    
        $pageSize = ArrayHelper::getValue(Yii::$app->request->get(), 'page_size', 20);
    
        $pagination = new Pagination([
            'defaultPageSize' => $pageSize,
            'totalCount' => $query->count(),
        ]);
        $assetList = $query->orderBy(["id" => SORT_DESC])
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        $data = [];
        /**
         * @var AssetList $v
         */
        foreach ($assetList as $k => $v) {
            $use = AssetListLog::find()->where([
                'asset_list_id' => $v->id,
                'person_id' => $v->person_id,
            ])->andWhere([
                'in', 'type', [2, 6]
            ])->one();
            if ($use) {
                $useDay = round((time()-$use->created_at)/3600/24).'天';
            } else {
                $useDay = '--';
            }
            /**
             * @var Asset $asset
             */
            $asset = $v->asset;
            $typeArr = explode('-', $asset->asset_type_name);
            $use_person = $org = '';
            if ($v->status == 2 && $v->person_id && $person = Person::findOne($v->person_id)) {
                $use_person = $person->person_name;
                $org= $person->org_full_name;
            }
            $data[] = [
                'index' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                'asset_id' => $v->id,
                'type' => $typeArr[0],
                'type_detail' => $typeArr[1],
                'brand_name' => $asset->asset_brand_name,
                'name' => $asset->name,
                'created_at' => date('Y-m-d H:i', $v->created_at),
                'stock_number' => $v->stock_number,
                'asset_number' => $v->asset_number,
                'sn_number' => $v->sn_number,
                'status_name' => AssetList::STATUS[$v->status],
                'status' => $v->status,
                'use_person' => $use_person,
                'org' => $org,
                'use_day' => $useDay,
            ];
        }
        return $this->_return([
            'list' => $data,
            'page' => BaseLogic::instance()->pageFix($pagination)
        ]);
    }

    public function actionDelAsset()
    {
        $model = new AssetListForm();
        $model->setScenario($model::SCENARIO_DEL);
        $param = \Yii::$app->request->post();
        $data['AssetListForm'] = $param;
        if ($model->load($data) && $model->validate() &&  $model->del($this->arrPersonInfo)) {
            return $this->_return([]);
        } else {
            return $this->_returnError(4400, null ,current($model->getFirstErrors()));
        }
    }
}