<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_asset_back".
 *
 * @property string $apply_id
 * @property integer $get_person
 * @property string $des
 * @property string $files
 * @property string $asset_list_ids
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
            [['files'], 'string'],
            [['apply_id'], 'string', 'max' => 20],
            [['get_person'], 'string', 'max' => 128],
            [['des','asset_list_ids'], 'string', 'max' => 255],
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
    
    /**
     * 获得固定资产归还说明
     * @param string $apply_id
     */
    public static function getDes($apply_id)
    {
        $des = '';//说明
        $model = static::find()->where(['apply_id'=>$apply_id])->one();
        $des = $model ? $model->des : $des;
        return $des;
    }
}
