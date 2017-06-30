<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_people_work_experience".
 *
 * @property integer $id
 * @property string $company_name
 * @property string $start_time
 * @property string $end_time
 * @property string $profession
 * @property integer $talent_id
 * @property integer $employee_id
 */
class PeopleWorkExperience extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_people_work_experience';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['talent_id', 'employee_id'], 'integer'],
            [['company_name', 'profession'], 'string', 'max' => 50],
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
            'company_name' => 'Company Name',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'profession' => 'Profession',
            'talent_id' => 'Talent ID',
            'employee_id' => 'Employee ID',
        ];
    }
}
