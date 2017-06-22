<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_asset_list_log".
 *
 * @property integer $id
 * @property integer $asset_list_id
 * @property integer $person_id
 * @property integer $type
 * @property string $des
 * @property integer $created_at
 */
class AssetListLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_asset_list_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['asset_list_id', 'person_id', 'type', 'created_at'], 'integer'],
            [['person_id', 'type', 'created_at'], 'required'],
            [['des'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'asset_list_id' => 'Asset List ID',
            'person_id' => '操作人',
            'type' => '类别',
            'des' => '说明',
            'created_at' => '时间',
        ];
    }
}
