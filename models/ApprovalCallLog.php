<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_approval_call_log".
 *
 * @property string $apply_id
 * @property integer $person_id
 * @property string $date
 * @property integer $success
 * @property string $data
 */
class ApprovalCallLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_approval_call_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id', 'person_id', 'date'], 'required'],
            [['person_id', 'success'], 'integer'],
            [['data'], 'string'],
            [['apply_id'], 'string', 'max' => 20],
            [['date'], 'string', 'max' => 8],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'apply_id' => 'Apply ID',
            'person_id' => 'Person ID',
            'date' => 'Date',
            'success' => '成功失败  0：失败 1：成功',
            'data' => 'Data',
        ];
    }
}
