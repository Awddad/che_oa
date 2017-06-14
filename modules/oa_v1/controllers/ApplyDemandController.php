<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/14
 * Time: 15:29
 */

namespace app\modules\oa_v1\controllers;

use app\models\Apply;
use app\modules\oa_v1\logic\BaseApplyLogic;
use app\modules\oa_v1\models\ApplyDemandForm;
use Yii;


/**
 * 需求单
 *
 * Class ApplyDemandController
 * @package app\modules\oa_v1\controllers
 */
class ApplyDemandController extends BaseController
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
        $model = new ApplyDemandForm();
        
        $param = \Yii::$app->request->post();
        $data['ApplyDemandForm'] = $param;
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
        /* @var Apply $apply */
        $apply = Apply::findOne($apply_id);
        if (empty($apply)) {
            return $this->_returnError(400, [], '未找到改报销');
        }
        $applyLogic = BaseApplyLogic::instance();
        $data['base'] = $applyLogic->getBaseApply($apply);
        $data['info'] = [
            'des' => $apply->applyDemand->des,
            'files' => json_decode($apply->applyDemand->files)
        ];
        
        $data['flow'] = BaseApplyLogic::instance()->getFlowData($apply);
        $data['demand_list'] = BaseApplyLogic::instance()->getApplyDemandList($apply->apply_id);
        return $this->_return($data);
    }
}