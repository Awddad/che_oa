<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_bao_xiao_list".
 * 报销申请附表 - 记录报销条目，如：住宿报销200元 餐饮报销100元等
 *
 * @property string $id
 * @property string $apply_id
 * @property string $money
 * @property string $type_name
 * @property integer $type
 * @property string $des
 */
class BaoXiaoList extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_bao_xiao_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id', 'type', 'des'], 'required'],
            [['money'], 'number'],
            [['type'], 'integer'],
            [['des'], 'string'],
            [['apply_id'], 'string', 'max' => 20],
            [['type_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增主键',
            'apply_id' => '申请单号-审批单流水号',
            'money' => '报销金额',
            'type_name' => '报销类别描述 - 可选项来自于财务',
            'type' => '报销类别id - 可选项来自于财务',
            'des' => '费用明细',
        ];
    }
}
