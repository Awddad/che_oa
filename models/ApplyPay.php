<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_pay".
 *
 * @property string $apply_id
 * @property integer $pay_type
 * @property string $money
 * @property string $des
 * @property string $to_name
 * @property string $card_number
 * @property string $bank_name
 * @property string $files
 * @property integer $created_at
 */
class ApplyPay extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_pay';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id'], 'required'],
            [['pay_type', 'created_at'], 'integer'],
            [['money'], 'number'],
            [['des', 'files'], 'string'],
            [['apply_id'], 'string', 'max' => 20],
            [['to_name'], 'string', 'max' => 128],
            [['card_number'], 'string', 'max' => 50],
            [['bank_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'apply_id' => '申请ID',
            'pay_type' => '付款类型',
            'money' => '付款金额',
            'des' => '付款事由',
            'to_name' => '对方名称',
            'card_number' => '对方卡号',
            'bank_name' => '开户行',
            'files' => '附件',
            'created_at' => '申请时间',
        ];
    }
}