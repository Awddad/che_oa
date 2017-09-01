<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_goods_up".
 *
 * @property string $apply_id
 * @property string $files
 * @property string $des
 * @property integer $shop_id
 * @property string $shop_name
 */
class GoodsUp extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_goods_up';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id'], 'required'],
            [['files'], 'string'],
            [['apply_id'], 'string', 'max' => 20],
            [['des'], 'string', 'max' => 255],
            [['shop_name'], 'string', 'max' => 64],
            ['shop_id', 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'apply_id' => '申请ID',
            'files' => '附件',
            'des' => '备注',
        ];
    }
    
    public function getGoodsUpDetail()
    {
        return $this->hasMany(GoodsUpDetail::className(), ['apply_id' => 'apply_id']);
    }
    /**
     * 获得商品上架说明
     */
    public function getDesInfo()
    {
        return '申请门店：'. $this->shop_name. '<br>申请说明：'.$this->des;
    }
}
