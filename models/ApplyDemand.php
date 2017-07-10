<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_demand".
 *
 * @property string $apply_id
 * @property string $des
 * @property string $files
 * @property integer $status
 * @property integer $buy_type
 * @property string $apply_buy_id
 * @property string $tips
 */
class ApplyDemand extends \yii\db\ActiveRecord
{
    const STATUS = [
        1 => '未采购',
        2 => '部分采购',
        3 => '已采购'
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_demand';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id'], 'required'],
            [['files'], 'string'],
            [['status', 'buy_type'], 'integer'],
            [['apply_id', 'apply_buy_id'], 'string', 'max' => 20],
            [['des'], 'string', 'max' => 512],
            [['tips'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'apply_id' => '申请ID',
            'des' => '说明',
            'files' => '附件',
            'status' => '请购单状态',
            'buy_type' => '请购类型， 1-请购全部 2-请购部分 3-库存已有',
            'apply_buy_id' => '请购ID',
            'tips' => '确认请购备注',
        ];
    }
}
