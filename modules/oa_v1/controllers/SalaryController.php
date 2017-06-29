<?php
namespace app\modules\oa_v1\controllers;

use app\modules\oa_v1\models\SalaryForm;
use yii\web\UploadedFile;
use yii;

/**
 * 薪酬
 * @author yjr
 */
class SalaryController extends BaseController
{
    /**
     * 导入
     */
    public function actionImport()
    {
        $file = UploadedFile::getInstanceByName('file');
        $model = new SalaryForm();
        $model->setScenario($model::SCENARIO_IMPORT);
        $model->file = $file;
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->import($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        } 
    }
    
    /**
     * 列表
     */
    public function actionGetList()
    {
        $post = yii::$app->request->post();
        $model = new SalaryForm();
        $res = $model->getList($post);
        return $this->_return($res);
    }
}