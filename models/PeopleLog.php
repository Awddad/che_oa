<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_people_log".
 *
 * @property integer $id
 * @property integer $talent_id
 * @property integer $employee_id
 * @property string $content
 * @property string $data
 * @property integer $person_id
 * @property string $person_name
 * @property integer $create_at
 */
class PeopleLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_people_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['talent_id', 'employee_id', 'person_id', 'create_at'], 'integer'],
            [['data'], 'string'],
            [['content'], 'string', 'max' => 100],
            [['person_name'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'talent_id' => 'Talent ID',
            'employee_id' => 'Employee ID',
            'content' => 'Content',
            'data' => 'Data',
            'person_id' => 'Person ID',
            'person_name' => 'Person Name',
            'create_at' => 'Create At',
        ];
    }
}
