<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_people_project_experience".
 *
 * @property integer $id
 * @property string $project_name
 * @property integer $company_id
 * @property string $project_profession
 * @property string $start_time
 * @property string $end_time
 * @property string $project_des
 * @property string $project_duty
 * @property integer $talent_id
 * @property integer $employee_id
 */
class PeopleProjectExperience extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_people_project_experience';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_name'], 'required'],
            [['company_id', 'talent_id', 'employee_id'], 'integer'],
            [['project_des', 'project_duty'], 'string'],
            [['project_name', 'project_profession'], 'string', 'max' => 50],
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
            'project_name' => 'Project Name',
            'company_id' => 'Company ID',
            'project_profession' => 'Project Profession',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'project_des' => 'Project Des',
            'project_duty' => 'Project Duty',
            'talent_id' => 'Talent ID',
            'employee_id' => 'Employee ID',
        ];
    }
    
    /**
     * 公司
     * @return ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(PeopleWorkExperience::className(), ['id'=>'company_id']);
    }
}
