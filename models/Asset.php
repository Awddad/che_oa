<?php

namespace app\models;

use MongoDB\BSON\Timestamp;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "oa_asset".
 *
 * @property integer $id
 * @property integer $asset_type_id
 * @property string $asset_type_name
 * @property integer $asset_brand_id
 * @property string $asset_brand_name
 * @property string $name
 * @property integer $amount
 * @property string $price
 * @property integer $free_amount
 * @property integer $is_delete
 * @property integer $created_at
 * @property integer $updated_at
 */
class Asset extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_asset';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['asset_type_id', 'asset_brand_id', 'amount', 'free_amount', 'created_at', 'updated_at', 'is_delete'], 'integer'],
            [['price'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['asset_type_name', 'asset_brand_name', 'name'], 'string', 'max' => 128],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增ID',
            'asset_type_id' => '类别ID',
            'asset_type_name' => '类别',
            'asset_brand_id' => '品牌ID',
            'asset_brand_name' => '品牌',
            'name' => '名称',
            'amount' => '数量',
            'price' => '单价',
            'free_amount' => '可用库存',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className()
        ];
    }
}
