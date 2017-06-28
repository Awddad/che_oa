<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_employee".
 *
 * @property integer $id
 * @property string $empno
 * @property string $name
 * @property integer $org_id
 * @property integer $profession
 * @property string $phone
 * @property string $email
 * @property string $id_card
 * @property string $nation
 * @property integer $political
 * @property string $work_time
 * @property string $native
 * @property integer $marriage
 * @property integer $status
 * @property integer $type
 * @property string $entry_time
 * @property string $leave_time
 * @property integer $educational
 * @property integer $current_location
 * @property string $birthday
 * @property integer $employee_type
 * @property integer $person_id
 */
class Employee extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_employee';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['org_id', 'profession', 'political', 'marriage', 'status', 'type', 'educational', 'current_location', 'employee_type', 'person_id'], 'integer'],
            [['empno', 'name', 'entry_time', 'leave_time'], 'string', 'max' => 20],
            [['phone'], 'string', 'max' => 12],
            [['email'], 'string', 'max' => 50],
            [['id_card', 'birthday'], 'string', 'max' => 25],
            [['nation', 'native'], 'string', 'max' => 15],
            [['work_time'], 'string', 'max' => 10],
            [['person_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'empno' => 'Empno',
            'name' => 'Name',
            'org_id' => 'Org ID',
            'profession' => 'Profession',
            'phone' => 'Phone',
            'email' => 'Email',
            'id_card' => 'Id Card',
            'nation' => 'Nation',
            'political' => 'Political',
            'work_time' => 'Work Time',
            'native' => 'Native',
            'marriage' => 'Marriage',
            'status' => 'Status',
            'type' => 'Type',
            'entry_time' => 'Entry Time',
            'leave_time' => 'Leave Time',
            'educational' => 'Educational',
            'current_location' => 'Current Location',
            'birthday' => 'Birthday',
            'employee_type' => 'Employee Type',
            'person_id' => 'Person ID',
        ];
    }
    
    /**
     * 员工类型
     * @return ActiveQuery
     */
    public function getEmployeeType()
    {
        return $this->hasOne(EmployeeType::className(), ['id'=>'employee_type']);
    }
    
    /**
     * 职位
     * @return ActiveQuery
     */
    public function getJob()
    {
        return $this->hasOne(Job::className(), ['id'=>'profession']);
    }
    
    /**
     * 帐号
     * @return ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(EmployeeAccount::className(),['employee_id'=>'id']);
    }
}
