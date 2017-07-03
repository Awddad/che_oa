<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_people_files".
 *
 * @property integer $id
 * @property string $varchar
 * @property integer $talent_id
 * @property integer $employee_id
 */
class PeopleFiles extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_people_files';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['talent_id', 'employee_id'], 'integer'],
            [['varchar'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'varchar' => 'Varchar',
            'talent_id' => 'Talent ID',
            'employee_id' => 'Employee ID',
        ];
    }
}
