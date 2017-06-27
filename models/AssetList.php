<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_asset_list".
 *
 * @property integer $id
 * @property integer $asset_id
 * @property string $asset_number
 * @property string $stock_number
 * @property string $sn_number
 * @property string $price
 * @property integer $status
 * @property integer $apply_buy_id
 * @property integer $created_at
 */
class AssetList extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_asset_list';
    }
    
    const STATUS = [
        1 => '未使用',
        2 => '使用中',
        3 => '已报废',
        4 => '已丢失',
    ];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['asset_id'], 'required'],
            [['asset_id', 'status', 'created_at'], 'integer'],
            [['price'], 'number'],
            ['apply_buy_id', 'string', 'max' => 20],
            [['asset_number', 'stock_number'], 'string', 'max' => 15],
            [['sn_number'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'asset_id' => '资产ID',
            'asset_number' => '资产编号',
            'stock_number' => '库存编号',
            'sn_number' => 'sn编号',
            'price' => '采购价格',
            'status' => '状态',
            'created_at' => '入库时间',
        ];
    }
    
    /**
     * 获取资产信息
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAsset()
    {
        return $this->hasOne(Asset::className(), ['id' => 'asset_id']);
    }
}
