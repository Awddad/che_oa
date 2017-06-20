<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_asset_back".
 *
 * @property integer $apply_id
 * @property integer $get_person
 * @property string $des
 * @property string $files
 */
class AssetBack extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_asset_back';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id', 'get_person'], 'required'],
            [['apply_id', 'get_person'], 'integer'],
            [['files'], 'string'],
            [['des'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'apply_id' => '申请ID',
            'get_person' => '接收人',
            'des' => '说明',
            'files' => '附件',
        ];
    }
}
