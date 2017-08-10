<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_buy".
 *
 * @property string $apply_id
 * @property string $money
 * @property string $to_name
 * @property string $bank_card_id
 * @property string $bank_name
 * @property string $bank_name_des
 * @property string $des
 * @property string $files
 * @property integer $status
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
    
    const STATUS = [
        0 => '未入库',
        1 => '部分入库',
        2 => '已入库',
    ];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id'], 'required'],
            [['money'], 'number'],
            [['files'], 'string'],
            [['status'], 'integer'],
            [['apply_id'], 'string', 'max' => 20],
            [['to_name', 'bank_name_des'], 'string', 'max' => 128],
            [['bank_card_id'], 'string', 'max' => 50],
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
            'bank_card_id' => '对方卡号',
            'bank_name' => '开户行',
            'bank_name_des' => '支行',
            'des' => '说明',
            'files' => '文件',
            'status' => '请购单入库状态',
        ];
    }
    
    public function getBuyList()
    {
        return $this->hasMany(ApplyBuyList::className(), ['apply_id' => 'apply_id']);
    }
}
