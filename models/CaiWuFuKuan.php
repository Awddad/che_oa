<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_cai_wu_fu_kuan".
 * 财务付款确认记录表
 * 
 * @property string $apply_id
 * @property integer $org_id
 * @property string $org_name
 * @property string $bank_card_id
 * @property string $bank_name
 * @property string $bank_name_des
 * @property integer $type
 * @property string $fu_kuan_id
 * @property integer $fu_kuan_time
 * @property string $tips
 * @property string $pics
 * @property integer $create_time
 * @property integer $is_told_cai_wu_success
 * @property integer $account_id
 *
 */
class CaiWuFuKuan extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_cai_wu_fu_kuan';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id', 'org_id', 'org_name', 'bank_card_id', 'fu_kuan_id', 'fu_kuan_time'], 'required'],
            [['org_id', 'type', 'fu_kuan_time', 'create_time', 'is_told_cai_wu_success'], 'integer'],
            [['tips', 'pics'], 'string'],
            [['apply_id'], 'string', 'max' => 20],
            [['org_name', 'bank_name', 'bank_name_des', 'fu_kuan_id'], 'string', 'max' => 255],
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
            'org_id' => '付款部门 - id',
            'org_name' => '付款部门 - 名称',
            'bank_card_id' => '付款账号',
            'bank_name' => '放款银行名称',
            'bank_name_des' => '放款银行名称 - 支行',
            'type' => '付款类型',
            'fu_kuan_id' => '付款流水号，银行流水号',
            'fu_kuan_time' => '付款时间',
            'tips' => '备注',
            'pics' => '图片附件，一些付款凭证的拍照上传,多个逗号分隔',
            'create_time' => '数据收录时间',
        ];
    }
}
