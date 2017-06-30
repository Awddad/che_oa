<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/23
 * Time: 13:51
 */

namespace app\commands;

use app\models\Asset;
use app\models\AssetBrand;
use app\models\AssetList;
use app\models\AssetListLog;
use app\models\AssetType;
use app\models\Person;
use app\modules\oa_v1\logic\AssetLogic;
use SebastianBergmann\CodeCoverage\Report\PHP;
use Yii;
use moonland\phpexcel\Excel;
use yii\console\Controller;

class AssetExportController extends Controller
{
    public $status = [
        '未使用' => 1,
        '使用中' => 2,
        '已报废' => 3,
        '已丢失' => 4,
    ];
    public function actionIndex()
    {
        $data = Excel::import(\Yii::$app->basePath.'/asset.xlsx', [
            'setFirstRecordAsKeys' => false,
            'setIndexSheetByName' => true,
        ]);
        array_shift($data);
        foreach ($data as $k => $v) {
            $assetTypeA = $this->getAssetType($v["A"]);
            $assetTypeB = $this->getAssetType($v["B"], $assetTypeA->id);
            $assetBrand = $this->getAssetBrand($v["C"]);
            $asset = Asset::find()->where(['name' => $v["D"]])->one();
            if(empty($asset)) {
                $asset = new Asset();
                $asset->asset_brand_id = $assetBrand->id;
                $asset->asset_brand_name = $v["C"];
                $asset->asset_type_id = $assetTypeB->id;
                $asset->asset_type_name = $v["A"] . '-'.$v["B"];
                $asset->name = $v["D"];
                $asset->price = $v['K'] ? : 0;
                $asset->save();
            }
            /**
             * @var Person $person
             */
            $person = Person::find()->where(['person_name' => $v['H']])->one();
    
            $personId = 0;
            $assetList = new AssetList();
            $assetList->asset_id = $asset->id;
            $assetList->asset_number = $v['E'] ? : '';
            $assetList->stock_number = $v['F'];
            $assetList->price = $v['K'] ? : 0;
            $assetList->status = $this->status[trim($v['G'])];
            $assetList->created_at = time();
            if($this->status[trim($v['G'])] == 2) {
                if($person) {
                    $personId = $person->person_id;
                    $assetList->person_id = $personId;
                    $des = $person->person_name.'使用中';
                } else {
                    $des = '未知-使用中';
                }
            }
            $assetList->save();
            $asset->amount += 1;
            if($v['G'] == '未使用') {
                $asset->free_amount += 1;
            }
            if($this->status[trim($v['G'])] == 1){
                $des = '首次入库';
            }
            if($this->status[trim($v['G'])] == 3){
                $des = '报废';
            }
            if($this->status[trim($v['G'])] == 4){
                $des = '丢失';
            }
            $desc = isset($v['L']) ?  trim($v['L']) : $des;
            $this->addAssetListLog($assetList->id, $desc, $personId);
            echo '第'.$k . '条' .PHP_EOL;
        }
    }
    
    public function getAssetType($name, $pid = 0)
    {
        $assetType = AssetType::find()->where(['name' => $name])->one();
        if(empty($assetType)) {
            $assetType = new AssetType();
            $assetType->name = $name;
            $assetType->parent_id = $pid;
            $assetType->has_child = 0;
            $assetType->save();
        }
        return $assetType;
    }
    
    public function getAssetBrand($name)
    {
        $assetBrand = AssetBrand::find()->where(['name' => $name])->one();
        if(empty($assetBrand)) {
            $assetBrand = new AssetBrand();
            $assetBrand->name = $name;
            $assetBrand->save();
        }
        return $assetBrand;
    }
    
    public function addAssetListLog($assetListId, $des = null, $personId = 0)
    {
        $log = new AssetListLog();
        $log->person_id = $personId;
        $log->asset_list_id = $assetListId;
        $log->type = 2;
        $log->des = $des;
        
        $log->created_at = time();
        if ($log->save()) {
            return true;
        }
        throw new \yii\base\Exception('日志保存失败');
    }
}