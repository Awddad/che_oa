<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/14
 * Time: 15:56
 */

namespace app\modules\oa_v1\controllers;


use app\modules\oa_v1\models\ApplyUseChapterForm;


/**
 * 用章
 * Class ApplyUseChapterController
 * @package app\modules\oa_v1\controllers
 */
class ApplyUseChapterController extends BaseController
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
        $model = new ApplyUseChapterForm();
        
        $param = \Yii::$app->request->post();
        $data['ApplyUseChapterForm'] = $param;
        if ($model->load($data) && $model->validate() &&  $model->save($this->arrPersonInfo)) {
            return $this->_return($model->apply_id);
        } else {
            return $this->_return($model->errors, 400);
        }
    }
    
   
}