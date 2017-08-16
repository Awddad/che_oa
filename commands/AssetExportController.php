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
use app\models\AssetGetList;
use app\models\AssetList;
use app\models\AssetListLog;
use app\models\AssetType;
use app\models\Person;
use app\modules\oa_v1\logic\BaseLogic;
use Yii;
use moonland\phpexcel\Excel;
use yii\console\Controller;

/**
 * Class AssetExportController
 * @package app\commands
 */
class AssetExportController extends Controller
{
    public $status = [
        '未使用' => 1,
        '空闲中' => 1,
        '使用' => 2,
        '使用中' => 2,
        '已报废' => 3,
        '报废' => 3,
        '已丢失' => 4,
    ];
    public function actionIndex()
    {
        $data = Excel::import(\Yii::$app->basePath.'/asset.xlsx', [
            'setFirstRecordAsKeys' => false,
            'setIndexSheetByName' => true,
        ]);
        array_shift($data);
        $i = 1;
        foreach ($data as $k => $v) {
            if(!$v["A"] || !$v["B"] || !$v["C"] || !$v["D"]) {
                continue;
            }
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
                $asset->price = 0;
                $asset->save();
            }
            if($this->status[trim($v['G'])] == 2) {
                /**
                 * @var Person $person
                 */
                $person = Person::find()->where([
                    'person_name' => $v['H'],
                    'phone' => $v['L'],
                ])->one();
                if($person) {
                    $personId = $person->person_id;
                    $des = $person->person_name.'使用中';
                } else {
                    $person = Person::find()->where([
                        'person_name' => $v['H'],
                    ])->one();
                    if($person) {
                        $personId = $person->person_id;
                        $des = $person->person_name.'使用中';
                    } else {
                        $personId = 0;
                        $des = '未知-使用中';
                    }
                }
            } else {
                $personId = 0;
            }
            $assetList = new AssetList();
            $assetList->id = $i;
            $assetList->asset_id = $asset->id;
            $assetList->asset_number = $v['E'] ? : $v['F'];
            $assetList->stock_number = $v['F'];
            $assetList->sn_number = $v['K'] ? : '';
            $assetList->price = $v['M'] ? : 0;
            $assetList->status = $this->status[trim($v['G'])];
            $assetList->created_at = time();
            $assetList->person_id = $personId;
            $assetList->save();
            $asset->price += trim($v['M']);
            $asset->amount += 1;
            $type = 2;
            if($this->status[trim($v['G'])] == 1) {
                $asset->free_amount += 1;
                $des = '首次入库';
                $type = 1;
            }
            if($this->status[trim($v['G'])] == 3){
                $des = '报废';
                $type = 3;
            }
            if($this->status[trim($v['G'])] == 4){
                $des = '丢失';
                $type = 4;
            }
            $desc = isset($v['N']) ?  trim($v['N']) : $des;
            $asset->save();
            $this->addAssetListLog($assetList->id, $type, $desc, $personId);
            if($personId && $assetList){
                $this->addAssetGetList($assetList->id, $asset->id, $personId);
            }
            echo '第'.$k . '条' .PHP_EOL;
            $i++;
        }
    }
}