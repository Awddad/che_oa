<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_people_ability".
 *
 * @property integer $id
 * @property string $ability_name
 * @property integer $level
 * @property integer $talent_id
 * @property integer $employee_id
 */
class PeopleAbility extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_people_ability';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['level', 'talent_id', 'employee_id'], 'integer'],
            [['ability_name'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ability_name' => 'Ability Name',
            'level' => 'Level',
            'talent_id' => 'Talent ID',
            'employee_id' => 'Employee ID',
        ];
    }
}
