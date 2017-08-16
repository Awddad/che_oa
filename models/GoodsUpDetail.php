<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_goods_up_detail".
 *
 * @property integer $id
 * @property string $apply_id
 * @property string $supplier
 * @property string $supplier_type
 * @property string $contacts
 * @property string $job
 * @property string $phone
 * @property string $has_bus_contracts
 * @property string $use
 * @property string $car_status
 * @property string $has_car
 * @property integer $brand_id
 * @property string $brand
 * @property integer $factory_id
 * @property string $factory
 * @property integer $series_id
 * @property string $series
 * @property integer $car_id
 * @property string $car
 * @property integer $out_color_id
 * @property string $out_color
 * @property integer $in_color_id
 * @property string $in_color
 * @property string $discharge
 * @property string $car_type
 * @property string $product_date
 * @property string $sales_city
 * @property string $price_effective_cycle
 * @property integer $number
 * @property string $source
 * @property string $kilometre
 * @property string $end_date
 * @property string $invoice_type
 * @property string $send_date
 * @property string $guide_price
 * @property string $sales_price
 * @property string $in_price
 * @property string $freight
 * @property string $shop_insurance
 * @property string $shop_insurance_type
 * @property string $shop_insurance_price
 */
class GoodsUpDetail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_goods_up_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'apply_id' ,'supplier', 'supplier_type', 'contacts', 'job', 'phone', 'has_bus_contracts',
                    'use', 'car_status', 'has_car', 'brand_id', 'brand', 'factory_id', 'factory', 'series_id', 'series',
                    'car_id', 'car', 'out_color_id', 'out_color', 'in_color_id', 'in_color', 'discharge', 'car_type',
                    'product_date', 'sales_city', 'price_effective_cycle', 'number', 'source', 'kilometre', 'end_date',
                    'invoice_type', 'send_date', 'guide_price', 'sales_price', 'in_price', 'freight', 'shop_insurance',
                ],
                'required'
            ],
            [['brand_id', 'factory_id', 'series_id', 'car_id', 'out_color_id', 'in_color_id', 'number'], 'integer'],
            [['end_date', 'send_date'], 'safe'],
            [['guide_price', 'sales_price', 'in_price', 'freight', 'shop_insurance_price'], 'number'],
            [['apply_id'], 'string', 'max' => 20],
            [['supplier'], 'string', 'max' => 128],
            [['supplier_type', 'contacts', 'job', 'has_bus_contracts', 'discharge', 'car_type', 'product_date', 'source'], 'string', 'max' => 32],
            [['phone'], 'string', 'max' => 15],
            [['use', 'brand', 'factory', 'series', 'car', 'out_color', 'in_color'], 'string', 'max' => 64],
            [['car_status', 'has_car'], 'string', 'max' => 8],
            [['sales_city', 'price_effective_cycle'], 'string', 'max' => 255],
            [['kilometre', 'invoice_type', 'shop_insurance', 'shop_insurance_type'], 'string', 'max' => 16],
            ['shop_insurance', 'checkInsurance']
        ];
    }
    
    public function checkInsurance($attribute)
    {
        if ($this->$attribute == '是') {
            if (!$this->shop_insurance_type || !$this->shop_insurance_price) {
                $this->addError($attribute, '参数错误');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'apply_id' => '申请ID',
            'supplier' => '供应商',
            'supplier_type' => '供应商类型',
            'contacts' => '联系人',
            'job' => '职务',
            'phone' => '电话',
            'has_bus_contracts' => '是同否提供公车合同',
            'use' => '用途',
            'car_status' => '车辆状态',
            'has_car' => '是否现车',
            'brand_id' => 'Brand ID',
            'brand' => '品牌',
            'factory_id' => 'Factory ID',
            'factory' => '厂商',
            'series_id' => 'Series ID',
            'series' => '车系',
            'car_id' => 'Car ID',
            'car' => '车型',
            'out_color_id' => 'Out Color ID',
            'out_color' => '外观颜色',
            'in_color_id' => 'In Color ID',
            'in_color' => '内饰颜色',
            'discharge' => '排放标准',
            'car_type' => '车辆类型',
            'product_date' => '生产日期',
            'sales_city' => '限售地',
            'price_effective_cycle' => '价格有效范围',
            'number' => '数量',
            'source' => '来源',
            'kilometre' => '公里数',
            'end_date' => '截止销售时间',
            'invoice_type' => '开票类型',
            'send_date' => '寄出时间',
            'guide_price' => '指导价',
            'sales_price' => '销售价',
            'in_price' => '进价',
            'freight' => '物流费',
            'shop_insurance' => '是否店保',
            'shop_insurance_type' => '店保类型',
            'shop_insurance_price' => '店保金额',
        ];
    }
}
