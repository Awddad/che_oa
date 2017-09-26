<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_retire".
 *
 * @property string $apply_id
 * @property integer $person_id
 * @property string $person_name
 * @property string $profession
 * @property string $tel
 * @property string $retire_date
 * @property string $des
 * @property integer $finance_status
 * @property integer $account_status
 * @property integer $work_status
 * @property integer $handover_person_id
 * @property string $handover
 * @property integer $is_execute
 * @property integer $execute_person_id
 * @property string $execute_person
 * @property string $leave_time
 * @property string $leave_des
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
            [['person_id', 'finance_status', 'account_status', 'work_status', 'handover_person_id', 'is_execute', 'execute_person_id', 'created_at'], 'integer'],
            [['files'], 'string'],
            [['apply_id', 'person_name', 'tel', 'handover', 'execute_person'], 'string', 'max' => 20],
            [['profession'], 'string', 'max' => 32],
            [['retire_date', 'leave_time'], 'string', 'max' => 10],
            [['des', 'leave_des'], 'string', 'max' => 1024],
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
            'profession' => '职位',
            'tel' => '手机号',
            'retire_date' => '辞退日期',
            'des' => '辞退备注',
            'finance_status' => '财务结算 0：未结算   1：已结算',
            'account_status' => '帐号交接 0：未交接  1：已交接',
            'work_status' => '工作交接 0：未交接  1：已交接',
            'handover_person_id' => '离职交接人id',
            'handover' => '离职交接人',
            'is_execute' => '是否已执行 0否 1有',
            'execute_person_id' => '执行人id',
            'execute_person' => '执行人名',
            'leave_time' => '离职日期',
            'leave_des' => '离职原因',
            'files' => 'Files',
            'created_at' => 'Created At',
        ];
    }
}
