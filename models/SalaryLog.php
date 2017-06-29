<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_salary_log".
 *
 * @property integer $id
 * @property string $data
 * @property string $person_name
 * @property integer $person_id
 * @property string $create_date
 * @property integer $create_time
 */
class SalaryLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_salary_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['data'], 'string'],
            [['person_id', 'create_time'], 'integer'],
            [['person_name'], 'string', 'max' => 20],
            [['create_date'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'data' => 'Data',
            'person_name' => 'Person Name',
            'person_id' => 'Person ID',
            'create_date' => 'Create Date',
            'create_time' => 'Create Time',
        ];
    }
}
