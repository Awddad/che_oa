<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_talent_log".
 *
 * @property integer $id
 * @property integer $talent_id
 * @property string $content
 * @property string $data
 * @property integer $created_at
 * @property string $person_name
 * @property integer $person_id
 */
class TalentLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_talent_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['talent_id', 'created_at', 'person_id'], 'integer'],
            [['data'], 'string'],
            [['content'], 'string', 'max' => 50],
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
            'content' => 'Content',
            'data' => 'Data',
            'created_at' => 'Created At',
            'person_name' => 'Person Name',
            'person_id' => 'Person ID',
        ];
    }
}
