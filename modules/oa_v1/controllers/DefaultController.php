<?php

namespace app\modules\oa_v1\controllers;

use app\logic\MyTcPdf;
use app\logic\server\Server;
use app\logic\server\ThirdServer;
use app\models\Apply;
use app\models\BaoXiaoList;
use app\models\JieKuan;
use app\models\Person;
use app\models\Role;
use app\modules\oa_v1\logic\PdfLogic;
use Yii;
use app\modules\oa_v1\logic\PersonLogic;
use app\models\Menu;
use app\logic\server\QuanXianServer;
use app\models\Educational;
use app\models\Political;
use app\models\PersonType;
use app\models\EmployeeType;


/**
 * Default controller for the `oa_v1` module
 */
class DefaultController extends BaseController
{
    /**
     * TEST
     *
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $data = ThirdServer::instance([
            'token' => Yii::$app->params['cai_wu']['token'],
            'baseUrl' => Yii::$app->params['cai_wu']['baseUrl']
        ])->getTagTree();
        return $this->_return($data);
    }

    /**
     * 报销
     */
    public function actionGetPerson()
    {
        $person = PersonLogic::instance()->getSelectPerson($this->arrPersonInfo);
        return $this->_return($person);
    }

    /**
     * 获取用户信息接口 - 网站进入的时候调用该接口
     * @return array
     */
    public function actionGetUserInfo()
    {
        $roleId = Yii::$app->session->get("ROLE_ID");
        $arrData = [
            'userinfo' => $this->arrPersonInfo,
            'roleName' => Role::findOne($roleId)->slug,
            'roleInfo' => (isset($this->arrPersonRoleInfo['roleInfo']) ? $this->arrPersonRoleInfo['roleInfo'] : []),
        ];
        return $this->_return($arrData);
    }

    /**
     * 获取申请id
     * @return array
     */
    public function actionGetApplyId()
    {
        $intType = intval(Yii::$app->request->get('type'));
        if(array_key_exists($intType, $this->type))
        {
            switch($intType)
            {
                case 1:
                    $id = date('YmdHis') . '01' . rand(100, 999);
                    break;
                case 2:
                    $id = date('YmdHis') . '02' . rand(100, 999);
                    break;
                case 3:
                    $id = date('YmdHis') . '03' . rand(100, 999);
                    break;
                case 4:
                    $id = date('YmdHis') . '04' . rand(100, 999);
                    break;
                case 5:
                    $id = date('YmdHis') . '05' . rand(100, 999);
                    break;
                case 6:
                    $id = date('YmdHis') . '06' . rand(100, 999);
                    break;
                case 7:
                    $id = date('YmdHis') . '07' . rand(100, 999);
                    break;
                case 8:
                    $id = date('YmdHis') . '08' . rand(100, 999);
                    break;
                case 9:
                    $id = date('YmdHis') . '09' . rand(100, 999);
                    break;
                case 10:
                    $id = date('YmdHis') . '10' . rand(100, 999);
                    break;
                case 11:
                    $id = date('YmdHis') . '11' . rand(100, 999);
                    break; 
                case 12:
                    $id = date('YmdHis') . '12' . rand(100, 999);
                    break; 
                case 13:
                    $id = date('YmdHis') . '13' . rand(100, 999);
                    break;    
            }
            return $this->_return(['apply_id' => $id]);
        }
        else
        {
            return $this->_return([], 403);
        }
    }

    /**
     * 单点登录跳回来的时候带上角色id，设置登录的角色信息
     * 单点登录的时候跳回来的时候不支持url中有 -  所以此处全部小写
     */
    public function actionSetroleinfo()
    {
        $intRoleId = intval(Yii::$app->request->get('role_id'));
        $arrRoleIds = explode(',', $this->arrPersonInfo->role_ids);
        //但用户只有一个角色的时候进入系统没有role_id参数
        if(empty($intRoleId) && count($arrRoleIds) >= 1)
        {
            $intRoleId = $arrRoleIds[0];
        }
        //设置权限
        if( $intRoleId
            && in_array($intRoleId, explode(',', $this->arrPersonInfo->role_ids)) //用户有该角色
            && $this->setUserRoleInfo($intRoleId, 'web',  true)) //设置角色信息成功
        {
            //保存session
            $session = Yii::$app->getSession();
            $session->set('role_id', $intRoleId);
            //设置权限成功 - 跳转到网站首页
            header('Location: /oa/index.html');
            exit();
        }
        else
        {
            //失败，跳到登录页面,重新选择权限
            header('Location: ' . Yii::$app->params['quan_xian']['auth_sso_login_url']);
            exit();
        }
    }

    //获取网站的全部目录
    public function actionGetAllMenu()
    {
        $list = Menu::find()->asArray()->all();
        return $this->_return($list);
    }

    /**
     * 退出登录
     */
    public function actionLoginOut()
    {
        //跳转到登出页面
        Yii::$app->getSession()->destroy();
        return $this->_return(['login_url' => Yii::$app->params['quan_xian']['auth_sso_login_url']]);
    }

    /**
     * 获取组织架构
     *
     * @return array
     */
    public function actionOrg()
    {
        $data = PersonLogic::instance()->getOrgs();
        return $this->_return($data);
    }
    
    /**
     * 获取 PDF
     * @param $apply_id
     *
     */
    public function actionGetPdf($apply_id)
    {
        $apply = Apply::findOne($apply_id);
        $pdf = [];
        switch ($apply->type){
            case 1:
                $pdf = PdfLogic::instance()->expensePdf($apply);
                break;
            case 2:
                $pdf = PdfLogic::instance()->loanPdf($apply);
                break;
            case 3:
                $pdf = PdfLogic::instance()->payBackPdf($apply);
                break;
            case 4:
                $pdf = PdfLogic::instance()->applyPayPdf($apply);
                break;
            case 5:
                $pdf = PdfLogic::instance()->applyBuyPdf($apply);
                break;
            case 6:
                $pdf = PdfLogic::instance()->applyDemand($apply);
                break;
            case 7:
                $pdf = PdfLogic::instance()->useChapter($apply);
                break;
            case 8:
                $pdf = PdfLogic::instance()->assetGet($apply);
                break;
            case 9:
                $pdf = PdfLogic::instance()->assetBack($apply);
                break;
            case 10:
                $pdf = PdfLogic::instance()->applyPositive($apply);
                break;
            case 11:
                $pdf = PdfLogic::instance()->applyLeave($apply);
                break;
            case 12:
                $pdf = PdfLogic::instance()->applyTransfer($apply);
                break;
            case 13:
                $pdf = PdfLogic::instance()->applyOpen($apply);
                break;
        }
        if(!empty($pdf)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.$pdf['name'].'"');
            header('Content-Transfer-Encoding: binary');
            readfile($pdf['path']);
        } else {
            echo '未找到文件';
        }
    }
    
    /**
     * 下载链接，前端无法设置下载
     *
     * @param $path
     * @param $name
     */
    public function actionDown($path, $name)
    {
        
        $rootPath = Yii::$app->basePath. '/web'.$path;
        if(file_exists($rootPath)){
            echo '未找到该文件';die;
        }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$name.'"');
        header('Content-Transfer-Encoding: binary');
        readfile($rootPath);
    }
    
    /**
     * 获取用户拥有的项目
     *
     * @return array
     */
    public function actionAllProjects()
    {
        $param = Yii::$app->params['quan_xian'];
        $personId = $this->arrPersonInfo->person_id;
        $url = $param['auth_api_url'].'/users/'.$personId.'/projects?_token='.$param['auth_token'];
        $data = Server::instance()->httpGet($url);
        if(!empty($data) && $data['success'] == true && !empty($data['data'])) {
            return $this->_return($data['data']);
        }
        return $this->_returnError(500, '', '未找到相关项目');
    }
    
    /**
     * 员工数据同步
     */
    public function actionSync()
    {
        $objQx = new QuanXianServer();
        $objQx->curlUpdateAllUser();
        return $this->_return(null);
    }
    /**
     * 获得政治面貌
     */
    public function actionGetPolitical()
    {
        $political = Political::find()->all();
        $data = [];
        foreach ($political as $v) {
            $data[] = [
                'label' => $v->political,
                'value' => $v->id,
            ];
        }
        return $this->_return($data);
    }
    
    /**
     * 获得员工类型
     */
    public function actionGetEmpType()
    {
        $empType = EmployeeType::find()->all();
        $data = [];
        foreach ($empType as $v) {
            $data[] = [
                'label' => $v->name,
                'value' => $v->id,
            ];
        }
        return $this->_return($data);
    }
}
