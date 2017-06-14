<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "oa_apply_positive".
 *
 * @property integer $id
 * @property string $apply_id
 * @property string $prosecution
 * @property string $summary
 * @property string $suggest
 * @property string $entry_time
 * @property string $job
 * @property string $files
 * @property integer $created_at
 */
class ApplyPositive extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_positive';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['files'], 'string'],
            [['created_at'], 'integer'],
            [['apply_id', 'job'], 'string', 'max' => 20],
            [['prosecution', 'summary', 'suggest'], 'string', 'max' => 255],
            [['entry_time'], 'string', 'max' => 25],
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
            'prosecution' => 'Prosecution',
            'summary' => 'Summary',
            'suggest' => 'Suggest',
            'entry_time' => 'Entry Time',
            'job' => 'Job',
            'files' => 'Files',
            'created_at' => 'Created At',
        ];
    }
}
