<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_people_train_experience".
 *
 * @property integer $id
 * @property string $train_place
 * @property string $start_time
 * @property string $end_time
 * @property string $tran_content
 * @property integer $talent_id
 * @property integer $employee_id
 */
class PeopleTrainExperience extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_people_train_experience';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tran_content'], 'string'],
            [['talent_id', 'employee_id'], 'integer'],
            [['train_place'], 'string', 'max' => 50],
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
            'train_place' => 'Train Place',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'tran_content' => 'Tran Content',
            'talent_id' => 'Talent ID',
            'employee_id' => 'Employee ID',
        ];
    }
}
