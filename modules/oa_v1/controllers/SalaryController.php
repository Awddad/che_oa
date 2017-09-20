<?php
namespace app\modules\oa_v1\controllers;

use app\modules\oa_v1\logic\RoleLogic;
use app\modules\oa_v1\models\SalaryForm;
use yii\web\UploadedFile;
use yii;
use app\modules\oa_v1\logic\SalaryLogic;

/**
 * 薪酬
 * @author yjr
 */
class SalaryController extends BaseController
{
    /**
     * 获得token
     */
    public function actionGetToken()
    {
        $post = yii::$app->request->post();
        $res = SalaryLogic::instance()->getTokenByPwd($this->arrPersonInfo, $post['pwd']);
        if($res){
            return $this->_return($res);
        }
        return $this->_returnError(400);
    }
    
    /**
     * 导入excel
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
        $get = yii::$app->request->get();
        $logic = SalaryLogic::instance();
        //if(!RoleLogic::instance()->isHr($this->arrPersonRoleInfo) && (!isset($get['_token']) || !$logic->checkToken($get['_token'], $this->arrPersonInfo))){
        if(!isset($get['_token']) || !$logic->checkToken($get['_token'], $this->arrPersonInfo)){
            return $this->_returnError(405,null,null);
        }
        $model = new SalaryForm();
        $res = $model->getList($get,$this->arrPersonInfo,$this->arrPersonRoleInfo);
        if(!$res){
            return $this->_returnError(404);
        }
        return $this->_return($res);
    }


    /**
     * 下载薪酬模版
     *
     * @param $path
     */
    public function actionDown()
    {
        $name = '薪酬'.date("Y-m").'.xls';
        $rootPath = Yii::$app->basePath. '/web/template/salary_template.xls';
        if(!file_exists($rootPath)){
            echo '未找到该文件';die;
        }
        header('Content-Type: application/x-xls');
        header('Content-Disposition: attachment; filename="'.$name.'"');
        header('Content-Transfer-Encoding: binary');
        readfile($rootPath);
        exit;
    }
}