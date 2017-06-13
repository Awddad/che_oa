<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_buy".
 *
 * @property string $apply_id
 * @property string $money
 * @property string $to_name
 * @property string $card_number
 * @property string $bank_name
 * @property string $des
 * @property string $files
 */
class ApplyBuy extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_buy';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id'], 'required'],
            [['money'], 'number'],
            [['files'], 'string'],
            [['apply_id'], 'string', 'max' => 20],
            [['to_name'], 'string', 'max' => 128],
            [['card_number'], 'string', 'max' => 50],
            [['bank_name'], 'string', 'max' => 64],
            [['des'], 'string', 'max' => 1024],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'apply_id' => '申请ID',
            'money' => '金额',
            'to_name' => '对方名称',
            'card_number' => '对方卡号',
            'bank_name' => '开户行',
            'des' => '说明',
            'files' => '文件',
        ];
    }
}
