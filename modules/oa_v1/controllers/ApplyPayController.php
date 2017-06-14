<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/13
 * Time: 11:15
 */

namespace app\modules\oa_v1\controllers;


use app\models\Apply;
use app\modules\oa_v1\logic\BaseApplyLogic;
use app\modules\oa_v1\models\ApplyPayForm;
use yii\filters\VerbFilter;

/**
 * 付款申请
 *
 * Class ApplyPayController
 * @package app\modules\oa_v1\controllers
 */
class ApplyPayController extends BaseController
{
    public function verbs()
    {
        return [
            'index' => ['post'],
            'view' => ['get']
        ];
    }
    
    /**
     * 申请付款
     */
    public function actionIndex()
    {
        $model = new ApplyPayForm();
        
        $param = \Yii::$app->request->post();
        $data['ApplyPayForm'] = $param;
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
        $applyLogic = BaseApplyLogic::instance();
        $data['base'] = $applyLogic->getBaseApply($apply);
        $data['info'] = [
            'to_name' => $apply->applyPay->to_name,
            'bank_card_id' => $apply->applyPay->bank_card_id,
            'bank_name' => $apply->applyPay->bank_name,
            'pay_type' => $apply->applyPay->pay_type,
            'des' => $apply->applyPay->des,
            'files' => json_decode($apply->applyBuy->files)
        ];
        $data['flow'] = $applyLogic->getFlowData($apply);
        return $this->_return($data);
    }
}