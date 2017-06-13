<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_buy_list".
 *
 * @property integer $id
 * @property string $apply_id
 * @property integer $asset_type_id
 * @property integer $asset_brand_id
 * @property string $name
 * @property string $price
 * @property integer $amount
 */
class ApplyBuyList extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_buy_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['asset_type_id', 'asset_brand_id', 'amount'], 'integer'],
            [['price'], 'number'],
            [['apply_id'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 128],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'apply_id' => '申请ID',
            'asset_type_id' => '资产类别',
            'asset_brand_id' => '品牌',
            'name' => '名称',
            'price' => '单价',
            'amount' => '数量',
        ];
    }
}
