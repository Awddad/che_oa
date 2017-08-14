<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_transfer".
 *
 * @property integer $id
 * @property string $apply_id
 * @property integer $old_org_id
 * @property string $old_org_name
 * @property integer $old_profession_id
 * @property string $old_profession
 * @property integer $target_org_id
 * @property string $target_org_name
 * @property integer $target_profession_id
 * @property string $target_profession
 * @property string $entry_time
 * @property string $transfer_time
 * @property string $des
 * @property string $files
 * @property integer $created_at
 */
class ApplyTransfer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_transfer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id'], 'required'],
            [['old_org_id', 'old_profession_id', 'target_org_id', 'target_profession_id', 'created_at'], 'integer'],
            [['files'], 'string'],
            [['apply_id', 'old_profession', 'target_profession'], 'string', 'max' => 20],
            [['old_org_name', 'target_org_name'], 'string', 'max' => 50],
            [['entry_time', 'transfer_time'], 'string', 'max' => 25],
            [['des'], 'string', 'max' => 1024],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'apply_id' => 'Apply ID',
            'old_org_id' => 'Old Org ID',
            'old_org_name' => 'Old Org Name',
            'old_profession_id' => 'Old Profession ID',
            'old_profession' => 'Old Profession',
            'target_org_id' => 'Target Org ID',
            'target_org_name' => 'Target Org Name',
            'target_profession_id' => 'Target Profession ID',
            'target_profession' => 'Target Profession',
            'entry_time' => 'Entry Time',
            'transfer_time' => 'Transfer Time',
            'des' => 'Des',
            'files' => 'Files',
            'created_at' => 'Created At',
        ];
    }

    /**
     * 获得调职说明
     * @param string $apply_id
     */
    public static function getDes($apply_id)
    {
        $des = '';//说明
        $model = static::find()->where(['apply_id'=>$apply_id])->one();
        $des = $model->des;
        return $des;
    }
}
