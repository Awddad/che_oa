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
use app\models\AssetType;
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
        $insertData = [];
        $num = 0;
        $free = 0;
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
            $num++;
            if($v['G'] == '未使用') {
                $free++;
            }
          
            $assetList = new AssetList();
            $assetList->asset_id = $asset->id;
            $assetList->asset_number = $v['E'] ? : '';
            $assetList->stock_number = $v['F'];
            $assetList->price = $v['K'] ? : 0;
            $assetList->status = $this->status[trim($v['G'])];
            $assetList->created_at = time();
            $assetList->save();
            echo '第'.$k . '条' .PHP_EOL;
        }
        $asset->amount = $num;
        $asset->free_amount = $free;
        $asset->save();
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
    
    public function getAsset($name)
    {
        
    }
}