<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_certificate".
 *
 * @property string $apply_id
 * @property integer $type
 * @property string $type_name
 * @property integer $org_id
 * @property string $start_time
 * @property string $end_time
 * @property string $des
 * @property string $files
 * @property integer $created_at
 */
class ApplyCertificate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_certificate';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id'], 'required'],
            [['type', 'org_id', 'created_at'], 'integer'],
            [['files'], 'string'],
            [['apply_id', 'type_name'], 'string', 'max' => 20],
            [['start_time', 'end_time'], 'string', 'max' => 10],
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
            'type' => '证件类型',
            'type_name' => '证件类型名称',
            'org_id' => '组织id',
            'start_time' => '使用开始时间',
            'end_time' => '使用结束时间',
            'des' => '使用事由',
            'files' => '附件jason',
            'created_at' => 'Created At',
        ];
    }

    /**
     * 获得离职说明
     */
    public function getDesInfo()
    {
        return $this->type_name.'-'.$this->des;
    }
}
