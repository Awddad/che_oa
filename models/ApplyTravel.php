<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_travel".
 *
 * @property string $apply_id
 * @property integer $total_day
 * @property string $des
 * @property string $files
 */
class ApplyTravel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_travel';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id', 'total_day'], 'required'],
            [['total_day'], 'integer'],
            [['files'], 'string'],
            [['apply_id'], 'string', 'max' => 20],
            [['des'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'apply_id' => 'Apply ID',
            'total_day' => '总天数',
            'des' => '出差事由',
            'files' => '附件',
        ];
    }
    
    /**
     * 出差申请列表
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTravelList()
    {
        return $this->hasMany(ApplyTravelList::className(), ['apply_id' => 'apply_id']);
    }
    
    /**
     * @param $applyId
     *
     * @return string
     */
    static public function getDes($applyId)
    {
        return self::findOne($applyId)->des;
    }
}
