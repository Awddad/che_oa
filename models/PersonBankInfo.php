<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_person_bank_info".
 * 公司员工银行卡信息表
 *
 * @property integer $id
 * @property string $bank_name
 * @property string $bank_name_des
 * @property string $bank_card_id
 * @property integer $is_salary
 * @property integer $person_id
 */
class PersonBankInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_person_bank_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'person_id'], 'required'],
            [['id', 'is_salary', 'person_id'], 'integer'],
            [['bank_name', 'bank_name_des'], 'string', 'max' => 255],
            [['bank_card_id'], 'string', 'max' => 25],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键id，同步与权限系统',
            'bank_name' => '银行名称',
            'bank_name_des' => '支行名称',
            'bank_card_id' => '银行卡号码',
            'is_salary' => '是否是工资卡：
0 - 不是
1 - 是',
            'person_id' => '员工id',
        ];
    }
}
