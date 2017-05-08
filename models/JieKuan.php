<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_jie_kuan".
 * 借款申请附表 - 记录借款申请的详情
 *
 * @property string $apply_id
 * @property string $money
 * @property string $des
 * @property string $tips
 * @property string $bank_card_id
 * @property string $bank_name
 * @property string $bank_name_des
 * @property integer $get_money_time
 * @property integer $pay_back_time
 * @property integer $is_pay_back
 * @property string $pics
 * @property integer $status
 */
class JieKuan extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_jie_kuan';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id', 'bank_card_id', 'bank_name', 'bank_name_des', 'get_money_time', 'pay_back_time', 'is_pay_back', 'pics', 'status'], 'required'],
            [['get_money_time', 'pay_back_time', 'is_pay_back', 'status'], 'integer'],
            [['money'], 'number'],
            [['des', 'tips'], 'string'],
            [['apply_id'], 'string', 'max' => 20],
            [['bank_name', 'bank_name_des', 'pics'], 'string', 'max' => 255],
            [['bank_card_id'], 'string', 'max' => 16],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'apply_id' => '申请id - 审核单流水号',
            'person' => '借款人姓名',
            'person_id' => '借款人id',
            'money' => '借款金额',
            'des' => '借款事由',
            'tips' => '备注',
            'bank_card_id' => '借款转入到的银行卡号',
            'bank_name' => '银行卡对应的银行 eg：工商银行',
            'bank_name_des' => '支行名称，如：中国工商银行丰庄支行',
            'get_money_time' => '借款到账时间 - 财务确认时间',
            'pay_back_time' => '还款时间 - 还款申请的财务确认时间',
            'is_pay_back' => '是否已还款',
            'pics' => '借款申请时上传的图片附件，多个用逗号分隔',
            'status' => '100 - 借款申请通过了，且已经提交过还款申请
101 - 还款成功
102 - 还款失败
',
        ];
    }
}
