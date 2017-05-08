<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/4
 * Time: 9:45
 */

namespace app\modules\oa_v1\controllers;


use yii\filters\auth\CompositeAuth;
use yii\filters\ContentNegotiator;
use yii\filters\RateLimiter;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\web\Response;
use Jasny\SSO\Broker;
use Yii;
use app\models\Person;
use app\models\Org;

/**
 * 接口基础
 *
 * Class BaseController
 * @package app\modules\oa_v1\controllers
 */
class BaseController extends Controller
{
    public $arrPersonInfo = [];//用户登录信息保存

    /**
     * @功能：项目入口，判断用户登录信息
     * @作者：王雕
     * @创建时间：2017-05-04
     * @param type $action
     * @return boolean
     */
    public function beforeAction($action){
        $strOsType = \Yii::$app->session->get('os_type', 'web');//默认是web版的
        if($strOsType == 'web')//web版的使用单点登录
        {
//            if(1)//需要后门，绕过单点登录，允许通过 site/login地址登录

            $serverUrl = Yii::$app->params['quan_xian']['auth_sso_url'];//单点登录地址
            $brokerId = Yii::$app->params['quan_xian']['auth_broker_id'];//项目appID
            $brokerSecret = Yii::$app->params['quan_xian']['auth_broker_secret'];//配置的项目 Secret
            $loginUrl = Yii::$app->params['quan_xian']['auth_sso_login_url'];
            $broker = new Broker($serverUrl, $brokerId, $brokerSecret);
            $broker->attach(true);
            $user = $broker->getUserInfo();//获取用户信息，这里会curl单点登录获取用户信息,但是不全
            if(!$user)
            {
                //跳转到单点登录地址
                $broker->clearToken();
                header('Location: ' . $loginUrl);
                exit();
            }
            else
            {
                $strCacheKey = 'login_' . $strOsType . '_' . $broker->token;
                $arrPerson = \Yii::$app->cache->get($strCacheKey);
                if(empty($arrPerson) || $arrPerson['person_id'] != $user['id'])
                {
                    $arrPerson = Person::findOne(['person_id' => $user['id']])->toArray();
                    if(empty($arrPerson))
                    {
                        //用户信息取不到的时候提示用户不存在
                        header("Content-type: application/json");
                        die(json_encode($this->_return(NULL, 402)));
                    }
                    \Yii::$app->cache->set($strCacheKey, $arrPerson);
                }
                $this->arrPersonInfo = $arrPerson;
            }
        }
        else
        {
            //app 版本的登录   先预留
        }
        return parent::beforeAction($action);
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'verbFilter' => [
                'class' => VerbFilter::className(),
                'actions' => $this->verbs(),
            ],
            'authenticator' => [
                'class' => CompositeAuth::className(),
            ],
            'rateLimiter' => [
                'class' => RateLimiter::className(),
            ],
        ];
    }
    

    public static $code = [
        200 => '成功',
        400 => '失败',
        401 => '未登录',
        402 => '用户不存在',
        403 => '参数错误',
        404 => '系统错误',
    ];

    /**
     * 统一返回格式
     *
     * @param string|array|object $data 返回内容
     * @param string $message
     * @param int $code
     * @return array
     */
    public function _return($data, $code = 200, $message = 'success')
    {
        $message = isset(static::$code[$code]) ? static::$code[$code] : $message;
        return compact('data', 'message', 'code');
    }
}