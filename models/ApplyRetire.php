<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_retire".
 *
 * @property string $apply_id
 * @property integer $person_id
 * @property string $person_name
 * @property string $retire_date
 * @property string $des
 * @property string $files
 * @property integer $created_at
 */
class ApplyRetire extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_retire';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id'], 'required'],
            [['person_id', 'created_at'], 'integer'],
            [['files'], 'string'],
            [['apply_id', 'person_name'], 'string', 'max' => 20],
            [['retire_date'], 'string', 'max' => 10],
            [['des'], 'string', 'max' => 1024],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'apply_id' => 'Apply ID',
            'person_id' => '被辞退人id',
            'person_name' => '被辞退人名',
            'retire_date' => '辞退日期',
            'des' => '辞退备注',
            'files' => 'Files',
            'created_at' => 'Created At',
        ];
    }
}
