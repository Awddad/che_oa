<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_holiday".
 *
 * @property string $apply_id
 * @property integer $type
 * @property string $type_name
 * @property string $start_time
 * @property string $end_time
 * @property integer $duration
 * @property string $des
 * @property string $files
 * @property integer $created_at
 */
class ApplyHoliday extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_holiday';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id', 'end_time'], 'required'],
            [['type', 'duration', 'created_at'], 'integer'],
            [['files'], 'string'],
            [['apply_id', 'type_name'], 'string', 'max' => 20],
            [['start_time', 'end_time'], 'string', 'max' => 20],
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
            'type' => '休假类型',
            'type_name' => '休假类型名字',
            'start_time' => '休假开始时间',
            'end_time' => 'End Time',
            'duration' => '休假时长（小时）',
            'des' => '休假事由',
            'files' => 'Files',
            'created_at' => 'Created At',
        ];
    }

    public function getDesInfo()
    {
        return $this->type_name.'-'.$this->duration.'小时-'.$this->des;
    }
}
