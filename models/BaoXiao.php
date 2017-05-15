<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_bao_xiao".
 * 报销申请附表 - 记录报销申请的内容
 *
 * @property string $id
 * @property string $apply_id
 * @property string $bao_xiao_list_ids
 * @property string $money
 * @property string $bank_card_id
 * @property string $bank_name
 * @property string $bank_name_des
 * @property string $files
 * @property string $pics
 */
class BaoXiao extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_bao_xiao';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id', 'bao_xiao_list_ids', 'bank_card_id', 'bank_name'], 'required'],
            [['money'], 'number'],
            [['files'], 'string'],
            [['apply_id'], 'string', 'max' => 20],
            [['bao_xiao_list_ids', 'bank_name', 'bank_name_des', 'pics'], 'string', 'max' => 255],
            [['bank_card_id'], 'string', 'max' => 16],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增主键',
            'apply_id' => '审批单流水号',
            'bao_xiao_list_ids' => '报销单子条目id，子条目信息存储在list表中',
            'money' => '报销总金额',
            'bank_card_id' => '报销时收款的银行卡号',
            'bank_name' => '收款银行',
            'bank_name_des' => '收款银行 - 支行',
            'files' => '附件信息,json格式存储
格式如下：
[
        {\'filename\' : \'明细word文档\', \'tag\' : \'doc\', \'url\' : \'http://****/***.doc\'},
        {\'filename\' : \'excel文档\', \'tag\' : \'xls\', \'url\' : \'http://****/***.xls\'},
    ]',
            'pics' => '图片url，多个用逗号分隔',
        ];
    }
}
