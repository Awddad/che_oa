<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "oa_asset_get_list".
 *
 * @property integer $id
 * @property string $apply_id
 * @property integer $person_id
 * @property integer $asset_id
 * @property integer $asset_list_id
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class AssetGetList extends \yii\db\ActiveRecord
{
    //申请中
    const STATUS_APPLY = 1;
    //已领用
    const STATUS_GET = 2;
    //审核失败
    const STATUS_GET_FAIL = 3;
    //申请归还中
    const STATUS_BACK_IN = 4;
    //已归还
    const STATUS_BACK_SUCCESS = 5;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_asset_get_list';
    }
    
    /**
     * 状态
     * @var array
     */
    const STATUS = [
        1 => '申请中',
        2 => '已领用',
        3 => '审核失败',
        4 => '申请归还中',
        5 => '已归还'
    ];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['asset_id', 'apply_id'], 'required'],
            [['asset_id', 'asset_list_id', 'status', 'created_at', 'updated_at', 'person_id'], 'integer'],
            [['apply_id'], 'string', 'max' => 20],
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
            'asset_id' => '固定资产ID',
            'asset_list_id' => '库存ID',
            'status' => '状态',
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
    
    /**
     * 获取资产信息
     * @return \yii\db\ActiveQuery
     */
    public function getAssetList()
    {
        return $this->hasOne(AssetList::className(), ['id' =>'asset_list_id']);
    }
    
    public function getAsset()
    {
        return $this->hasOne(Asset::className(), ['id' => 'asset_id']);
    }
}
