<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/8/11
 * Time: 14:09
 */

namespace app\modules\oa_v1\models;


use app\logic\server\PhoneLetter;
use app\models\ApprovalLog;
use app\models\GoodsUp;
use app\models\GoodsUpDetail;
use app\models\Person;
use app\modules\oa_v1\logic\BaseLogic;
use yii\db\Exception;

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
    
    public $shop_id;
    
    public $shop_name;
    
    /**
     * 验证数据
     */
    public function rules()
    {
        return [
            [['approval_persons', 'apply_id', 'goods_list', 'shop_id', 'shop_name'], 'required',],
            [['approval_persons', 'copy_person'], 'each', 'rule' => ['integer']],
            [['approval_persons', 'copy_person'], 'checkTotal'],
            ['des', 'string'],
            ['files', 'safe'],
            ['apply_id', 'checkOnly'],
        ];
    }
    
    public function attributeLabels()
    {
        return [
            'approval_persons' => '审批人',
            'copy_person' => '审批人',
            'goods_list' => '商品列表',
            'shop_id' => '销售门店',
            'shop_name' => '销售门店',
        ];
    }
    
    /**
     * Goods List
     * @return array
     */
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
            'kilometre', //公里数
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
    
    /**
     * 申请单
     *
     * @param Person $person
     *
     * @return string
     * @throws Exception
     */
    public function save($person)
    {
        $apply = $this->setApply($person);
        
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!$apply->save()) {
                throw new Exception('商品上架申请创建失败', $apply->errors);
            }
            $this->GoodsUpSave();
            $this->GoodsUpListSave();
            $this->approvalPerson($apply);
            $this->copyPerson($apply);
            $transaction->commit();

            $nextApproval = ApprovalLog::findOne(['apply_id'=>$this->apply_id,'is_to_me_now'=>1]);
            $content = "您好，{$nextApproval->approval_person}，{$apply->person}提交了一条商品上架审批，请及时处理。";
            $tel = Person::findOne($nextApproval->approval_person_id)->phone;
            PhoneLetter::instance()->sendYY($tel, $content);

            return $apply->apply_id;
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
    
    /**
     * 商品上架表
     *
     * @return bool
     * @throws Exception
     */
    public function GoodsUpSave()
    {
        $goodsUp = new GoodsUp();
        $data['GoodsUp'] = [
            'apply_id' => $this->apply_id,
            'files' => json_encode($this->files),
            'des' => $this->des,
            'shop_id' => $this->shop_id,
            'shop_name' => $this->shop_name,
        ];
        if ($goodsUp->load($data) && $goodsUp->save()) {
            return true;
        } else {
            throw new Exception(BaseLogic::instance()->getFirstError($goodsUp->errors));
        }
    }
    
    /**
     * 商品详情
     *
     * @return bool
     * @throws Exception
     */
    public function GoodsUpListSave()
    {
        $goodsUpList = new GoodsUpDetail();
        foreach ($this->goods_list as $v) {
            $v['apply_id'] = $this->apply_id;
            $data['GoodsUpDetail'] = $v;
            $model = clone $goodsUpList;
            if (!$model->load($data) || !$model->save()) {
                throw new Exception(BaseLogic::instance()->getFirstError($model->errors));
            }
        }
        return true;
    }
}