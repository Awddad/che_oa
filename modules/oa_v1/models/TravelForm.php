<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/8/24
 * Time: 10:32
 */

namespace app\modules\oa_v1\models;


use app\models\ApplyTravel;
use app\models\ApplyTravelList;
use app\modules\oa_v1\logic\BaseLogic;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * 出差申请表单
 *
 * Class TravelForm
 * @package app\modules\oa_v1\models
 */
class TravelForm extends BaseForm
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
    public $type = 15;
    
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
    
    public $total_day = 0;
    
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
    public $travel_list = [];
    
    
    public function rules()
    {
        return [
            [['apply_id', 'des', 'travel_list', 'approval_persons'], 'required'],
            [['approval_persons', 'copy_person'], 'each', 'rule' => ['integer']],
            [['approval_persons', 'copy_person'], 'checkTotal'],
            ['des', 'string', 'max' => 1000],
            ['files', 'safe'],
            ['total_day', 'integer'],
            ['apply_id', 'checkOnly'],
        ];
    }
    
    
    public function attributeLabels()
    {
        return [
            'apply_id' => '申请单号',
            'approval_persons' => '审批人',
            'copy_person' => '审批人',
            'travel_list' => '出差列表',
            'dsc' => '出差事由'
        ];
    }
    
    /**
     * 保存
     *
     * @param $person
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
            $this->travelSave();
            $this->travelListSave();
            $this->approvalPerson($apply);
            $this->copyPerson($apply);
            $transaction->commit();
            return $apply->apply_id;
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
    
    /**
     * 保存出差
     *
     * @return bool
     * @throws Exception
     */
    public function travelSave()
    {
        $goodsUp = new ApplyTravel();
        $data['ApplyTravel'] = [
            'apply_id' => $this->apply_id,
            'files' => json_encode($this->files),
            'des' => $this->des,
            'total_day' => $this->getTotalDay(),
        ];
        if ($goodsUp->load($data) && $goodsUp->save()) {
            return true;
        } else {
            throw new Exception(BaseLogic::instance()->getFirstError($goodsUp->errors));
        }
    }
    
    /**
     * 出差列表
     *
     * @return bool
     * @throws Exception
     */
    public function travelListSave()
    {
        $goodsUpList = new ApplyTravelList();
        foreach ($this->travel_list as $v) {
            // 时间
            $time = isset($v['date_time']) ? $v['date_time'] : '';
            $beginAt = $endAt = '';
            if (!empty($time) && strlen($time > 20)) {
                $beginAt = substr($time, 0, 10);
                $endAt = substr($time, -10);
            }
            //$v['apply_id'] = $this->apply_id;
            $data['ApplyTravelList'] = [
                'apply_id' => $this->apply_id,
                'address' => isset($v['address']) ? $v['address'] : '',
                'begin_at' => $beginAt,
                'end_at' => $endAt ,
                'day' => isset($v['day']) ? $v['day'] : '',
            ];
            $model = clone $goodsUpList;
            if (!$model->load($data) || !$model->save()) {
                throw new Exception(BaseLogic::instance()->getFirstError($model->errors));
            }
        }
        return true;
    }
    
    /**
     * 总天数
     *
     * @return float|int
     */
    public function getTotalDay()
    {
        return array_sum(ArrayHelper::getColumn($this->travel_list,'day'));
    }
}