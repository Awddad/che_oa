<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "oa_pay_back".
 * 还款申请附表 - 记录还款申请详情
 *
 * @property string $apply_id
 * @property string $jie_kuan_ids
 * @property string $des
 * @property string $money
 * @property string $bank_card_id
 * @property string $bank_name
 * @property string $bank_name_des
 */
class PayBack extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_pay_back';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id', 'jie_kuan_ids'], 'required'],
            [['des'], 'string'],
            [['money'], 'number'],
            [['apply_id'], 'string', 'max' => 20],
            [['jie_kuan_ids', 'bank_name', 'bank_name_des'], 'string', 'max' => 255],
            [['bank_card_id'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'apply_id' => '申请id - 审核单流水号',
            'jie_kuan_ids' => '关联的借款单id，多个用逗号分隔',
            'des' => '还款说明',
            'money' => '还款总金额,几个借款单的总金额',
            'bank_card_id' => '还款银行卡号',
            'bank_name' => '还款银行',
            'bank_name_des' => '还款银行 - 支行',
        ];
    }

    public function getApply()
    {
        return $this->hasOne(Apply::className(), ['apply_id' => 'apply_id']);
    }
    
    /**
     * 获得还款说明
     */
    public function getDesInfo()
    {
        $jiekuanList = JieKuan::find()->where(['in','apply_id',explode(',', $this->jie_kuan_ids)])->all();
        $total = 0;
        /**
         * @var JieKuan $v
         */
        foreach ($jiekuanList as $k => $v){
            $des[] = ($k+1) .'、' .$v->des .'-'. \Yii::$app->formatter->asCurrency($v->money);
            $total += $v->money;
        }
        $des[] = '合计金额-'. \Yii::$app->formatter->asCurrency($total);
        return implode('<br>', $des);
    }
    
}
