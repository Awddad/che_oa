<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/13
 * Time: 11:15
 */

namespace app\modules\oa_v1\controllers;



use app\modules\oa_v1\models\ApplyPayForm;

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
    
    
}