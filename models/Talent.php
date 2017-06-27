<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_talent".
 *
 * @property integer $id
 * @property string $name
 * @property string $phone
 * @property integer $job
 * @property integer $age
 * @property string $birthday
 * @property integer $sex
 * @property integer $educational
 * @property integer $person_type
 * @property string $work_time
 * @property integer $current_location
 * @property integer $status_communion
 * @property integer $status_test
 * @property integer $status_face
 * @property integer $status 
 * @property integer $talent
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $employee_id
 */
class Talent extends \yii\db\ActiveRecord
{
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
            [['job', 'age', 'sex', 'educational', 'person_type', 'current_location', 'status_communion', 'status_test', 'status_face', 'status', 'talent', 'created_at', 'updated_at', 'employee_id'], 'integer'],
            [['name'], 'string', 'max' => 20],
            [['phone'], 'string', 'max' => 12],
            [['birthday'], 'string', 'max' => 25],
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
            'name' => 'Name',
            'phone' => 'Phone',
            'job' => 'Job',
            'age' => 'Age',
            'birthday' => 'Birthday',
            'sex' => 'Sex',
            'educational' => 'Educational',
            'person_type' => 'Person Type',
            'work_time' => 'Work Time',
            'current_location' => 'Current Location',
            'status_communion' => 'Status Communion',
            'status_test' => 'Status Test',
            'status_face' => 'Status Face',
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
    	return $this->hasOne(Job::className(), ['id' => 'job']);
    }
    
    /**
     * 学历
     * @return ActiveQuery
     */
    public function getEdu()
    {
    	return $this->hasOne(Educational::className(), ['id' => 'educational']);
    }
    
    /**
     * 人才类型
     * @return ActiveQuery
     */
    public function getPersonType()
    {
    	return $this->hasOne(PersonType::className(),['id'=>'person_type']);
    }
    
    /**
     * 当前所在地
     * @return ActiveQuery
     */
    public function getCurrentLocation()
    {
    	return $this->hasOne(Region::className(),['id'=>'current_location']);
    }
}
