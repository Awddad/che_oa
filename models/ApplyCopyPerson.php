<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_copy_person".
 * 申请抄送人表
 * 
 * @property string $apply_id
 * @property integer $copy_person_id
 * @property string $copy_person
 */
class ApplyCopyPerson extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_copy_person';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id', 'copy_person_id'], 'required'],
            [['copy_person_id'], 'integer'],
            [['apply_id'], 'string', 'max' => 20],
            [['copy_person'], 'string', 'max' => 255],
            [['apply_id', 'copy_person_id'], 'unique', 'targetAttribute' => ['apply_id', 'copy_person_id'], 'message' => 'The combination of 申请单号，审批流水号 and 抄送人的姓名 has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'apply_id' => '申请单号，审批流水号',
            'copy_person_id' => '抄送人的姓名',
            'copy_person' => '抄送人姓名',
        ];
    }
    
    public function getApply()
    {
    	return $this -> hasOne(Apply::className(), ['apply_id' => 'apply_id']);
    }
}
