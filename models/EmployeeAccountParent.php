<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "oa_employee_account_parent".
 *
 * @property integer $id
 * @property integer $person_id
 * @property integer $employee_id
 * @property string $name
 * @property string $relation
 * @property string $idnumber
 * @property string $bank_name
 * @property string $bank_card
 * @property integer $created_at
 * @property integer $updated_at
 */
class EmployeeAccountParent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_employee_account_parent';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['person_id', 'employee_id', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 32],
            [['relation'], 'string', 'max' => 1],
            [['idnumber'], 'string', 'max' => 18],
            [['bank_name'], 'string', 'max' => 128],
            [['bank_card'], 'string', 'max' => 50],
            [['person_id', 'employee_id', 'name', 'relation', 'idnumber', 'bank_name', 'bank_card'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'person_id' => 'Person ID',
            'employee_id' => '员工ID',
            'name' => '姓名',
            'relation' => '关系',
            'idnumber' => '身份证',
            'bank_name' => '开户行',
            'bank_card' => '银行卡号',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className()
        ];
    }
    
    public function getEmployee()
    {
        return $this->hasOne(Employee::className(), ['id' => 'employee_id']);
    }
}
