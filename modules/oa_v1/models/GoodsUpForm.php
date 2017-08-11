<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/8/11
 * Time: 14:09
 */

namespace app\modules\oa_v1\models;


class GoodsUpForm extends BaseForm
{
    /**
     * 是否需要财务确认
     * @var
     */
    public $cai_wu_need = 1;
    
    /**
     * 申请ID
     * @var
     */
    public $apply_id;
    
    /**
     * 商品上架
     * @var int
     */
    public $type = 14;
    
    /**
     * 附件
     * @var
     */
    public $files;
    
    /**
     * 描述
     * @var string
     */
    public $des = '';
    
    /**
     * 审批人
     * @var array
     */
    public $approval_persons = [];
    
    /**
     * 抄送人
     * @var array
     */
    public $copy_person = [];
    
    /**
     * 商品列表
     * @var array
     */
    public $goods_list = [];
    
    /**
     *
     */
    public function rules()
    {
        return [
            [
                ['approval_persons', 'apply_id', 'goods_list'],
                'required',
                'message' => '缺少必填字段'
            ],
            [
                ['approval_persons', 'copy_person'],
                'each',
                'rule' => ['integer']
            ],
            [
                ['approval_persons', 'copy_person'], 'checkTotal'
            ],
            ['des', 'string'],
            ['files', 'safe'],
            ['apply_id', 'checkOnly'],
            ['goods_list', 'checkGoodsList']
        ];
    }
    
    public function checkGoodsList($attribute)
    {
        $goodsList = $this->$attribute;
        if(!is_array($goodsList) || empty($goodsList)) {
            $this->addError($attribute, '商品列表不能空');
        }
        
        foreach ($goodsList as $v) {
            foreach ($this->arrayLabel() as $val) {
                if (!isset($v[$val]) || empty($v[$val])) {
                    $this->addError($attribute, '商品列表信息错误');
                }
            }
        }
    }
    
    public function arrayLabel()
    {
        return [
            'supplier', //供应商
            'supplier_type', //供应商类型
            'contacts', //联系人
            'job', //职务
            'phone', //电话
            'has_bus_contracts', //是同否提供公车合同
            'use', //车辆用途
            'car_status', //车辆状态
            'has_car',// 是否现车
            'brand', //品牌
            'factory', //厂商
            'series', //车系
            'car', //车型
            'out_color', //外观颜色
            'in_color', //内饰颜色
            'discharge', //排放标准
            'car_type', //车辆类型
            'product_date', //生产日期
            'sales_city', //限售地
            'price_effective_cycle', //价格有效周期
            'number', //数量
            'source', //来源
            'kilometre', //来源
            'end_date', //截止销售时间
            'invoice_type', //开票类型
            'send_date', //寄出时间
            'guide_price', //指导价
            'sales_price', //销售价
            'in_price', //进价
            'freight', //物流费
            'shop_insurance', //是否店保
            'shop_insurance_type', //店保类型
            'shop_insurance_price', //店保金额
            
        ];
    }
}