<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "oa_talent".
 *
 * @property integer $id
 * @property integer $owner
 * @property string $name
 * @property string $phone
 * @property string $email
 * @property integer $job
 * @property string $now_salary
 * @property string $want_salary
 * @property string $yingpin_location
 * @property integer $age
 * @property string $birthday
 * @property integer $sex
 * @property integer $educational
 * @property string $nation
 * @property string $native
 * @property integer $political
 * @property integer $marriage
 * @property integer $job_status
 * @property integer $person_type
 * @property string $daogang
 * @property string $work_time
 * @property integer $current_location
 * @property integer $status_communion
 * @property integer $status_test
 * @property integer $status_face
 * @property integer $status
 * @property string $disagree_reason 
 * @property integer $talent
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $employee_id
 */
class Talent extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_talent';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['job', 'owner', 'age', 'sex', 'educational', 'political', 'marriage', 'job_status', 'person_type', 'current_location', 'status_communion', 'status_test', 'status_face', 'status', 'talent', 'created_at', 'updated_at', 'employee_id'], 'integer'],
            [['name', 'daogang'], 'string', 'max' => 20],
            [['phone'], 'string', 'max' => 12],
            [['email', 'yingpin_location','disagree_reason'], 'string', 'max' => 100],
            [['now_salary', 'want_salary', 'work_time'], 'string', 'max' => 10],
            [['birthday'], 'string', 'max' => 25],
            [['nation', 'native'], 'string', 'max' => 15],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'owner' => 'Owner',
            'name' => 'Name',
            'phone' => 'Phone',
            'email' => 'Email',
            'job' => 'Job',
            'now_salary' => 'Now Salary',
            'want_salary' => 'Want Salary',
            'yingpin_location' => 'Yingpin Location',
            'age' => 'Age',
            'birthday' => 'Birthday',
            'sex' => 'Sex',
            'educational' => 'Educational',
            'nation' => 'Nation',
            'native' => 'Native',
            'political' => 'Political',
            'marriage' => 'Marriage',
            'job_status' => 'Job Status',
            'person_type' => 'Person Type',
            'daogang' => 'Daogang',
            'work_time' => 'Work Time',
            'current_location' => 'Current Location',
            'status_communion' => 'Status Communion',
            'status_test' => 'Status Test',
            'status_face' => 'Status Face',
            'status' => 'Status',
            'disagree_reason' => 'Disagree Reason',
            'talent' => 'Talent',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'employee_id' => 'Employee ID',
        ];
    }
    /**
     * 职位
     * @return ActiveQuery
     */
    public function getProfession()
    {
        return $this->hasOne(Job::className(), ['id'=>'job']);
    }
    
    /**
     * 学历
     * @return ActiveQuery
     */
    public function getEdu()
    {
        return $this->hasOne(Educational::className(), ['id'=>'educational']);
    }
    
    /**
     * 人才类型
     * @return ActiveQuery
     */
    public function getPersonType()
    {
        return $this->hasOne(PersonType::className(), ['id'=>'person_type']);
    }
}
