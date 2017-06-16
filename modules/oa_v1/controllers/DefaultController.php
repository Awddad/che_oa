<?php

namespace app\modules\oa_v1\controllers;

use app\logic\server\ThirdServer;
use Yii;
use app\modules\oa_v1\logic\PersonLogic;
use app\models\Menu;


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
        $person = PersonLogic::instance()->getSelectPerson($this->arrPersonInfo['person_id']);
        return $this->_return($person);
    }

    /**
     * 获取用户信息接口 - 网站进入的时候调用该接口
     * @return array
     */
    public function actionGetUserInfo()
    {
        $arrData = [
            'userinfo' => $this->arrPersonInfo,
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
}
