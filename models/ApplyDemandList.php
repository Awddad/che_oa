<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_demand_list".
 *
 * @property integer $id
 * @property string $apply_id
 * @property string $name
 * @property integer $amount
 */
class ApplyDemandList extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_demand_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['amount'], 'integer'],
            [['apply_id'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 128],
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
            'name' => '名称',
            'amount' => '数量',
        ];
    }
}
