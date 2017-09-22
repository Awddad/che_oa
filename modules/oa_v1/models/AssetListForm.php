<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/27
 * Time: 18:07
 */

namespace app\modules\oa_v1\models;


use app\models\Asset;
use app\models\AssetList;
use app\models\Person;
use app\modules\oa_v1\logic\AssetLogic;
use yii\base\Model;
use yii\db\Exception;

/**
 * 资产库存列表
 *
 * Class AssetListForm
 * @package app\modules\oa_v1\models
 */
class AssetListForm extends Model
{
    const SCENARIO_ADD = 'add';//添加
    const SCENARIO_DEL = 'del';//删除


    public $asset_type_id;
    
    public $asset_brand_id;
    
    public $name;
    
    public $price;
    
    public $amount;

    public $asset_id;

    public $is_delete=0;
    
    public function rules()
    {
        return [
            [['asset_type_id', 'asset_brand_id', 'name', 'price', 'amount'], 'required', 'on' => [self::SCENARIO_ADD]],
            [['asset_id'],'required', 'on' => [self::SCENARIO_DEL]],
            ['asset_id','exist','targetClass'=>'\app\models\Asset','targetAttribute'=>['asset_id'=>'id','is_delete'=>'is_delete'],'message'=>'库存不存在！'],
            [['asset_type_id', 'asset_brand_id', 'amount'], 'integer'],
            [['name', 'amount'], 'string']
        
        ];
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_ADD => ['asset_type_id', 'asset_brand_id', 'name', 'price', 'amount'],
            self::SCENARIO_DEL =>['asset_id']
        ];
    }
    
    /**
     * 新增库存
     *
     * @param Person $person
     *
     * @return Asset
     * @throws Exception
     */
    public function save($person)
    {
        /**
         * @var Asset $asset
         */
        $asset = Asset::find()->where([
            'asset_type_id' => $this->asset_type_id,
            'asset_brand_id' => $this->asset_brand_id,
            'name' => $this->name,
            'is_delete' => 0,
            //'price' => $this->price,
        ])->one();
        $assetLogic = AssetLogic::instance();
        if(empty($asset)) {
            $asset = new Asset();
            $asset->asset_type_id = $this->asset_type_id;
            $asset->asset_type_name = $assetLogic->getAssetType($this->asset_type_id);
            $asset->asset_brand_id = $this->asset_brand_id;
            $asset->asset_brand_name = $assetLogic->getAssetBrand($this->asset_brand_id);
            $asset->name = $this->name;
            $asset->amount = $this->amount;
            $asset->price = $this->price;
            $asset->free_amount = $this->amount;
        } else {
            $asset->amount += $this->amount;
            $asset->free_amount += $this->amount;
            $asset->price += $this->amount * $this->price;
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!$asset->save()) {
                throw new Exception('入库失败', $asset->errors);
            }
            $assetLogic->addAssetList($asset, $this->amount, $person, $this->price);
            $transaction->commit();
            return $asset;
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param $person Person
     * @return bool
     */
    public function del($person)
    {
        $query = AssetList::find()->select('status,count(*) count')->where(['asset_id'=>$this->asset_id])->groupBy('status');
        $data = $query->asArray()->all();
        $error = '';
        foreach($data as $v) {
            if (!$error){
                switch(intval($v['status'])){
                    case 2:
                        $error = '有人使用不可删除！';
                        break;
                    case 5:
                        $error = '有人申请使用不可删除！';
                        break;
                    default:
                        break;
                }
            }
        }
        if($error){
            $this->addError('',$error);
            return false;
        }
        $asset = Asset::findOne($this->asset_id);
        $asset->is_delete=1;
        if(!$asset->save()){
            $this->addError('',current($asset->getFirstErrors()));
            return false;
        }
        return true;
    }
}