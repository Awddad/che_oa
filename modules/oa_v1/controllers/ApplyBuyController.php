<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/13
 * Time: 13:52
 */

namespace app\modules\oa_v1\controllers;


use app\models\Apply;
use app\modules\oa_v1\logic\BaseApplyLogic;
use app\modules\oa_v1\models\ApplyBuyForm;

/**
 * 申请请购
 *
 * Class ApplyBuyController
 * @package app\modules\oa_v1\controllers
 */
class ApplyBuyController extends BaseController
{
    /**
     * @return array
     */
    public function verbs()
    {
        return [
            'index' => ['post'],
            'view' => ['get']
        ];
    }
    
    /**
     * 申请请购
     */
    public function actionIndex()
    {
        $model = new ApplyBuyForm();
        
        $param = \Yii::$app->request->post();
        $data['ApplyBuyForm'] = $param;
        if ($model->load($data) && $model->validate() &&  $model->save($this->arrPersonInfo)) {
            return $this->_return($model->apply_id);
        } else {
            return $this->_return($model->errors, 400);
        }
    }
    
    /**
     * 请购详情
     *
     * @param $apply_id
     * @return array
     */
    public function actionView($apply_id)
    {
        $apply = Apply::findOne($apply_id);
        if (empty($apply)) {
            return $this->_returnError(400, [], '未找到改报销');
        }
        $data = [
            "apply_id" => $apply->apply_id,
            "create_time" => date('Y-m-d H:i', $apply->create_time),
            "next_des" => $apply->next_des,
            "title" => $apply->title,
            "type" => $apply->type,
            "type_value" => "请购",
            "person" => $apply->person,
            'date' => date('Y年m月d日', $apply->create_time),
            'to_name' => $apply->applyBuy->to_name,
            'bank_card_id' => $apply->applyBuy->bank_card_id,
            'bank_name' => $apply->applyBuy->bank_name,
            'des' => $apply->applyBuy->des
        ];
        
        $data['copy_person'] = $apply->copy_person;
        $data['flow'] = BaseApplyLogic::instance()->getFlowData($apply);
        $data['buy_list'] = BaseApplyLogic::instance()->getApplyBuyList($apply->apply_id);
        return $this->_return($data);
    }
}