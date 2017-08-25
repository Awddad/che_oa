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
 * @property string $face_time
 * @property integer $need_test
 * @property integer $choice_score
 * @property integer $answer_score
 * @property integer $talent
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $employee_id
 */
class Talent extends \yii\db\ActiveRecord
{
    const STATUS = [
        1 => '待沟通',
        2 => '待考试',
        3 => '待面试',
        4 => '不合适',
        5 => '录用'
    ];
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
            [['owner', 'job', 'age', 'sex', 'educational', 'political', 'marriage', 'job_status', 'person_type', 'current_location', 'status_communion', 'status_test', 'status_face', 'status', 'need_test', 'choice_score', 'answer_score', 'talent', 'created_at', 'updated_at', 'employee_id'], 'integer'],
            [['name', 'daogang', 'face_time'], 'string', 'max' => 20],
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
            'owner' => '创建人',
            'name' => '姓名',
            'phone' => '手机号',
            'email' => '邮箱',
            'job' => '应聘职位',
            'now_salary' => 'Now Salary',
            'want_salary' => 'Want Salary',
            'yingpin_location' => 'Yingpin Location',
            'age' => '年龄',
            'birthday' => 'Birthday',
            'sex' => '性别',
            'educational' => '学历',
            'nation' => 'Nation',
            'native' => 'Native',
            'political' => 'Political',
            'marriage' => 'Marriage',
            'job_status' => 'Job Status',
            'person_type' => 'Person Type',
            'daogang' => 'Daogang',
            'work_time' => '工作年限',
            'current_location' => '所在地',
            'status_communion' => 'Status Communion',
            'status_test' => 'Status Test',
            'status_face' => 'Status Face',
            'status' => '状态',
            'disagree_reason' => 'Disagree Reason',
            'face_time' => 'Face Time',
            'need_test' => 'Need Test',
            'choice_score' => 'Choice Score',
            'answer_score' => 'Answer Score',
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
