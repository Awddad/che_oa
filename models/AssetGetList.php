<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "oa_asset_get_list".
 *
 * @property integer $id
 * @property string $apply_id
 * @property integer $asset_id
 * @property integer $asset_list_id
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class AssetGetList extends \yii\db\ActiveRecord
{
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
    public $status = [
        1 => '申请中',
        2 => '已发放',
        3 => '已归还'
    ];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['asset_id', 'apply_id'], 'required'],
            [['asset_id', 'asset_list_id', 'status', 'created_at', 'updated_at'], 'integer'],
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
}
