<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_region".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property integer $type
 * @property string $name
 * @property string $fullName
 */
class Region extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_region';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'parent_id', 'type', 'name', 'fullName'], 'required'],
            [['id', 'parent_id', 'type'], 'integer'],
            [['name', 'fullName'], 'string', 'max' => 128],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Parent ID',
            'type' => 'Type',
            'name' => 'Name',
            'fullName' => 'Full Name',
        ];
    }
}
