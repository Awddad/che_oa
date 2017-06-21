<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_open".
 *
 * @property integer $apply_id
 * @property integer $district
 * @property string $address
 * @property string $rental
 * @property string $files
 * @property string $summary
 * @property integer $created_at
 */
class ApplyOpen extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_open';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id'], 'required'],
            [['apply_id', 'district', 'created_at'], 'integer'],
            [['rental'], 'number'],
            [['files'], 'string'],
            [['address'], 'string', 'max' => 50],
            [['summary'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'apply_id' => 'Apply ID',
            'district' => 'District',
            'address' => 'Address',
            'rental' => 'Rental',
            'files' => 'Files',
            'summary' => 'Summary',
            'created_at' => 'Created At',
        ];
    }
}
