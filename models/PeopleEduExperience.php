<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_people_edu_experience".
 *
 * @property integer $id
 * @property string $school_name
 * @property string $major
 * @property string $start_time
 * @property string $end_time
 * @property integer $educational
 * @property integer $talent_id
 * @property integer $employee_id
 */
class PeopleEduExperience extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_people_edu_experience';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['educational', 'talent_id', 'employee_id'], 'integer'],
            [['school_name', 'major'], 'string', 'max' => 50],
            [['start_time', 'end_time'], 'string', 'max' => 7],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'school_name' => 'School Name',
            'major' => 'Major',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'educational' => 'Educational',
            'talent_id' => 'Talent ID',
            'employee_id' => 'Employee ID',
        ];
    }
    
    /**
     * 学历
     * @return ActiveQuery
     */
    public function getEdu()
    {
        return $this->hasOne(Educational::className(), ['id'=>'educational']);
    }
}
