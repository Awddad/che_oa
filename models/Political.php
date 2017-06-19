<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_political".
 *
 * @property integer $id
 * @property string $political
 */
class Political extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_political';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['political'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'political' => 'Political',
        ];
    }
}
