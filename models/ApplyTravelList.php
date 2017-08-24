<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_travel_list".
 *
 * @property integer $id
 * @property string $apply_id
 * @property string $address
 * @property string $begin_at
 * @property string $end_at
 * @property integer $day
 */
class ApplyTravelList extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_travel_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['begin_at', 'end_at', 'day'], 'required'],
            [['begin_at', 'end_at'], 'safe'],
            [['day'], 'integer'],
            [['apply_id'], 'string', 'max' => 20],
            [['address'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'apply_id' => '申请ID',
            'address' => '出差地点',
            'begin_at' => '开始时间',
            'end_at' => '结束时间',
            'day' => '天数',
        ];
    }
}
