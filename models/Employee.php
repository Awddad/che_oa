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
 * @property integer $sex
 * @property string $id_card
 * @property string $nation
 * @property integer $political
 * @property string $work_time
 * @property string $native
 * @property integer $marriage
 * @property integer $status
 * @property string $entry_time
 * @property string $leave_time
 * @property integer $educational
 * @property integer $current_location
 * @property integer $age
 * @property string $birthday
 * @property integer $employee_type
 * @property string $service
 * @property integer $service_id
 * @property integer $person_id
 *
 * @property EmployeeType $employeeType
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
            [['org_id', 'profession', 'sex', 'political', 'marriage', 'status', 'educational', 'current_location', 'age', 'employee_type', 'service_id', 'person_id'], 'integer'],
            [['empno', 'name', 'entry_time', 'leave_time'], 'string', 'max' => 20],
            [['phone'], 'string', 'max' => 12],
            [['email', 'service'], 'string', 'max' => 50],
            [['id_card', 'birthday'], 'string', 'max' => 25],
            [['nation', 'native'], 'string', 'max' => 15],
            [['work_time'], 'string', 'max' => 10],
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
            'sex' => 'Sex',
            'id_card' => 'Id Card',
            'nation' => 'Nation',
            'political' => 'Political',
            'work_time' => 'Work Time',
            'native' => 'Native',
            'marriage' => 'Marriage',
            'status' => 'Status',
            'entry_time' => 'Entry Time',
            'leave_time' => 'Leave Time',
            'educational' => 'Educational',
            'current_location' => 'Current Location',
            'age' => 'Age',
            'birthday' => 'Birthday',
            'employee_type' => 'Employee Type',
            'service' => 'Service',
            'service_id' => 'Service ID',
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
     * 组织
     * @return ActiveQuery
     */
    public function getOrg()
    {
        return $this->hasOne(Org::className(), ['org_id'=>'org_id']);
    }
    
    /**
     * 帐号
     * @return ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(EmployeeAccount::className(),['employee_id'=>'id']);
    }
    
    /**
     * 银行卡
     * @return ActiveQuery
     */
    public function getBankCard()
    {
        if($this->person_id){
            return $this->hasMany(PersonBankInfo::className(),['person_id'=>'person_id']);
        }else{
            return null;
        }
    }
}
