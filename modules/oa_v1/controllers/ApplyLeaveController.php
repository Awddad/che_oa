<?php
namespace app\modules\oa_v1\controllers;

use yii;
use app\modules\oa_v1\models\ApplyLeaveForm;
use app\modules\oa_v1\logic\AssetLogic;
use app\modules\oa_v1\logic\JieKuanLogic;
use app\models\Employee;

/**
 * 离职
 * @author yjr
 *
 */
class ApplyLeaveController extends BaseController
{
    public function actionAddApply()
    {
        $post = yii::$app->request->post();
        $model = new ApplyLeaveForm();
        $model->load(['ApplyLeaveForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->saveApply($this->arrPersonInfo);
        if($res['status']){
            return $this->_return($res['apply_id']);
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    /**
     * 获得名下信息（固定资产 财务 帐号）
     */
    public function actionGetList()
    {
        $employee = Employee::find()->where(['person_id'=>$this->arrPersonInfo['person_id']])->one();
        $array = [
            'stock_list' => AssetLogic::instance()->getAssetHistory($this->arrPersonInfo['person_id']),
            'finance_list' => JieKuanLogic::instance()->getHistory($this->arrPersonInfo['person_id']),
            'qq' => isset($employee->account) ? $employee->account->qq : '',
            'email' => isset($employee->account) ? $employee->account->email : '',
            'tel' => isset($employee->account)?$employee->account->tel:'',
       ];
       return $this->_return($array);
    }
}