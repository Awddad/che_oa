<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_educational".
 *
 * @property integer $id
 * @property string $educational
 */
class Educational extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_educational';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['educational'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'educational' => 'Educational',
        ];
    }
}
