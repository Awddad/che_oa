<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "oa_employee_type".
 *
 * @property integer $id
 * @property string $name
 * @property integer $add_time
 * @property integer $update_time
 */
class EmployeeType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_employee_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['add_time', 'update_time'], 'integer'],
            [['name'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
    
    public function behaviors()
    {
    	return [
    			'timestamp'=>[
    					'class' => TimestampBehavior::className(),
    					'createdAtAttribute' => 'add_time',
    					'updatedAtAttribute' => 'update_time',
    			]
    	];
    }
}
