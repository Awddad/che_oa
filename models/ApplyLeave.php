<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_leave".
 *
 * @property integer $apply_id
 * @property string $leave_time
 * @property string $des
 * @property integer $stock_status
 * @property integer $finance_status
 * @property integer $account_status
 * @property integer $work_status
 * @property string $files
 * @property integer $created_at
 */
class ApplyLeave extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_leave';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id'], 'required'],
            [['apply_id', 'stock_status', 'finance_status', 'account_status', 'work_status', 'created_at'], 'integer'],
            [['files'], 'string'],
            [['leave_time'], 'string', 'max' => 25],
            [['des'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'apply_id' => 'Apply ID',
            'leave_time' => 'Leave Time',
            'des' => 'Des',
            'stock_status' => 'Stock Status',
            'finance_status' => 'Finance Status',
            'account_status' => 'Account Status',
            'work_status' => 'Work Status',
            'files' => 'Files',
            'created_at' => 'Created At',
        ];
    }
}
