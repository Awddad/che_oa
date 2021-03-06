<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/1
 * Time: 14:16
 */

namespace app\modules\oa_v1\controllers;


use app\models\Person;
use app\modules\oa_v1\logic\BaseLogic;
use Jasny\SSO\Broker;
use Yii;
use yii\helpers\ArrayHelper;
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
        //清除cookie 
        $this->clearSsoToken();
        $session->removeAll();
        /**
         * @var Person $objPerson
         */
        $objPerson = $session->get('USER_INFO');
        $param = Yii::$app->request->get();
        $osType = ArrayHelper::getValue($param, 'os_type', 'web');
        $uid = Yii::$app->request->get('uid');
       
        if (empty($objPerson)) {
            if($osType == 'web') {
                $loginUrl = Yii::$app->params['quan_xian']['auth_sso_login_url'];
                $broker = BaseLogic::instance()->ssoClient();
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
            } elseif ($osType == 'crm') {
                $time = Yii::$app->request->get('time');
                $sign = Yii::$app->request->get('sign');
                if ($sign == md5($osType.$uid.$time.'che.com')) {
                    $objPerson = Person::findOne(['person_id' => $uid]);
                    if (empty($objPerson)) {
                        die('<h3>您没OA权限，请联系管理员</h3>');
                    }
                    $session->set('USER_INFO', $objPerson);
                }
            }
        }
        if ($osType == 'crm') {
            // 清除session
            Yii::$app->getSession()->destroy();
            $time = Yii::$app->request->get('time');
            $sign = Yii::$app->request->get('sign');
            if ($sign == md5($osType . $uid . $time . 'che.com')) {
                $objPerson = Person::findOne(['person_id' => $uid]);
                if (empty($objPerson)) {
                    die('<h3>您没OA权限，请联系管理员</h3>');
                }
                $session->set('USER_INFO', $objPerson);
            }
        }
        
        $intRoleId = intval(Yii::$app->request->get('role_id'));
        $arrRoleIds = explode(',', $objPerson->role_ids);

        if ($intRoleId && in_array($intRoleId, $arrRoleIds)) {
            $session->set('ROLE_ID', $intRoleId);
        } else {
            $session->set('ROLE_ID', $arrRoleIds[0]);
        }
        if($osType == 'crm') {
            header('Location: /oa/index.html#/adminhome?isnav=0');
        } else {
            header('Location: /oa/index.html#/adminhome');
        }
        exit();
    }
    
    /**
     * clear sso token
     */
    public function clearSsoToken()
    {
        $broker = BaseLogic::instance()->ssoClient();
        $broker->clearToken();
    }
}