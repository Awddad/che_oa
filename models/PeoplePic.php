<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_people_pic".
 *
 * @property integer $id
 * @property string $pic
 * @property integer $talent_id
 * @property integer $employee_id
 */
class PeoplePic extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_people_pic';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['talent_id', 'employee_id'], 'integer'],
            [['pic'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pic' => 'Pic',
            'talent_id' => 'Talent ID',
            'employee_id' => 'Employee ID',
        ];
    }
}
