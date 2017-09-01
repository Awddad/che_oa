<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_open".
 *
 * @property integer $apply_id
 * @property integer $district
 * @property string $district_name
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
            [['district_name', 'address'], 'string', 'max' => 50],
            [['summary'], 'string', 'max' => 1024],
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
            'district_name' => 'District Name',
            'address' => 'Address',
            'rental' => 'Rental',
            'files' => 'Files',
            'summary' => 'Summary',
            'created_at' => 'Created At',
        ];
    }
    
    /**
     * 获取该审批的申请信息
     * @return \yii\db\ActiveQuery|Apply
     */
    public function getApply()
    {
        return $this->hasOne(Apply::className(), ['apply_id' => 'apply_id']);
    }

    /**
     * 获得开店说明
     */
    public function getDesInfo()
    {
        return $this->summary;
    }
}
