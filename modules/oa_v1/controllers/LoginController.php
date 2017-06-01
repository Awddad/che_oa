<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/1
 * Time: 14:16
 */

namespace app\modules\oa_v1\controllers;


use app\models\Person;
use Jasny\SSO\Broker;
use Yii;
use yii\rest\Controller;

/**
 * SSO 登陆后逻辑
 *
 * Class LoginController
 * @package app\modules\oa_v1\controllers
 */
class LoginController extends Controller
{
    /**
     *
     */
    public function actionIndex()
    {
        $session = Yii::$app->session;
        $objPerson = $session->get('USER_INFO');
        if (empty($objPerson)) {
            $serverUrl = Yii::$app->params['quan_xian']['auth_sso_url'];//单点登录地址
            $brokerId = Yii::$app->params['quan_xian']['auth_broker_id'];//项目appID
            $brokerSecret = Yii::$app->params['quan_xian']['auth_broker_secret'];//配置的项目 Secret
            $loginUrl = Yii::$app->params['quan_xian']['auth_sso_login_url'];
            $broker = new Broker($serverUrl, $brokerId, $brokerSecret);
            $broker->attach(true);
            $user = $broker->getUserInfo();//获取用户信息，这里会curl单点登录获取用户信息,但是不全
            if (!$user) {
                //用户没有登录 需要跳转到登录页面去登录
                $broker->clearToken();
                header("Location:" . $loginUrl);
                die();
            }
            $objPerson = Person::findOne(['person_id' => $user['id']]);
            if (empty($objPerson)) {
                $broker->clearToken();
                header("Location:" . $loginUrl);
                die();
            }
            $session->set('USER_INFO', $objPerson);
        }
        $intRoleId = intval(Yii::$app->request->get('role_id'));
        $arrRoleIds = explode(',', $objPerson->role_ids);

        if ($intRoleId && in_array($intRoleId, $arrRoleIds)) {
            $session->set('ROLE_ID', $intRoleId);
        } else {
            $session->set('ROLE_ID', $arrRoleIds[0]);
        }
        header('Location: /oa/index.html');
        exit();
    }
}