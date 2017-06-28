<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_cai_wu_shou_kuan".
 * 财务收款记录表
 * 
 * @property string $apply_id
 * @property integer $org_id
 * @property string $org_name
 * @property string $bank_card_id
 * @property string $bank_name
 * @property string $bank_name_des
 * @property integer $type
 * @property string $shou_kuan_id
 * @property integer $shou_kuan_time
 * @property string $tips
 * @property integer $create_cai_wu_log
 * @property string $pics
 * @property string $is_told_cai_wu_success
 * @property string $account_id
 */
class CaiWuShouKuan extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_cai_wu_shou_kuan';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id', 'org_id', 'org_name', 'bank_card_id', 'bank_name', 'bank_name_des', 'shou_kuan_id'], 'required'],
            [['org_id', 'type', 'shou_kuan_time', 'create_cai_wu_log', 'is_told_cai_wu_success'], 'integer'],
            [['tips', 'pics'], 'string'],
            [['apply_id', 'org_name', 'bank_name', 'bank_name_des', 'shou_kuan_id', 'pics'], 'string', 'max' => 255],
            [['bank_card_id'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'apply_id' => '申请单号，审批单流水号',
            'org_id' => '收款部门 - id',
            'org_name' => '收款部门 - 名称',
            'bank_card_id' => '收款账号 - 银行卡号',
            'bank_name' => '收款银行',
            'bank_name_des' => '收款银行 - 支行',
            'type' => '收款类型',
            'shou_kuan_id' => '收款流水号',
            'shou_kuan_time' => '收款时间',
            'tips' => '备注',
            'create_cai_wu_log' => '是否自动生成财务流水：
1 - 自动生成
0 - 不生成',
            'pics' => '付款凭证图片，多个用逗号分隔',
        ];
    }
}
