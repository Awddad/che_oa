<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "oa_employee_account".
 *
 * @property integer $id
 * @property integer $employee_id
 * @property string $qq
 * @property string $email
 * @property string $tel
 * @property integer $created_at
 * @property integer $updated_at
 */
class EmployeeAccount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_employee_account';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['employee_id'], 'required'],
            [['employee_id', 'created_at', 'updated_at'], 'integer'],
            [['qq'], 'string', 'max' => 20],
            [['email'], 'string', 'max' => 50],
            [['tel'], 'string', 'max' => 11],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'employee_id' => 'Employee ID',
            'qq' => 'Qq',
            'email' => 'Email',
            'tel' => 'Tel',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
    
    public function behaviors()
    {
    	return [TimestampBehavior::className()];
    }
}
