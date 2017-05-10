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
     *不做登录校验的请求的白名单 controller/action格式
     * @var array 
     * Yii::$app->controller->module->id    模块名称
     * Yii::$app->controller->id            控制器名称
     * Yii::$app->controller->action->id    方法名称
     */
    private static $arrWhiteList  = [
        'default/get-user-info',
    ];




    /**
     * @功能：项目入口，判断用户登录信息
     * @作者：王雕
     * @创建时间：2017-05-04
     * @param type $action
     * @return boolean
     */
    public function beforeAction($action){
        
        $this->arrPersonInfo =  Person::findOne(['person_id' => 257]);
        return parent::beforeAction($action);
//        $strOsType = \Yii::$app->session->get('os_type', 'web');//默认是web版的
//        if($strOsType == 'web')//web版的使用单点登录
//        {
//            $serverUrl = Yii::$app->params['quan_xian']['auth_sso_url'];//单点登录地址
//            $brokerId = Yii::$app->params['quan_xian']['auth_broker_id'];//项目appID
//            $brokerSecret = Yii::$app->params['quan_xian']['auth_broker_secret'];//配置的项目 Secret
//            $loginUrl = Yii::$app->params['quan_xian']['auth_sso_login_url'];
//            $broker = new Broker($serverUrl, $brokerId, $brokerSecret);
//            $broker->attach(true);
//            $user = $broker->getUserInfo();//获取用户信息，这里会curl单点登录获取用户信息,但是不全
//            if(!$user)
//            {
//                //在白名单中的没有登录的也可以请求
//                $strUri = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;
//                if(in_array(strtolower($strUri), self::$arrWhiteList))
//                {
//                    return parent::beforeAction($action);
//                }
//                else
//                {
//                    //跳转到单点登录地址
//                    $broker->clearToken();
//                    header('Location: ' . $loginUrl);
//                    exit();
//                }
//            }
//            else
//            {
//                $strCacheKey = 'login_' . $strOsType . '_' . $broker->token;
//                $arrPerson = \Yii::$app->cache->get($strCacheKey);
//                if(empty($arrPerson) || $arrPerson['person_id'] != $user['id'])
//                {
//                    $objPerson = Person::findOne(['person_id' => $user['id']]);
//                    if(!$objPerson)
//                    {
//                        //用户信息取不到的时候提示用户不存在
//                        header("Content-type: application/json");
//                        die(json_encode($this->_return(NULL, 402)));
//                    }
//                    else
//                    {
//                        $arrPerson = $objPerson->toArray();
//                        Yii::$app->cache->set($strCacheKey, $arrPerson);
//                    }
//                }
//                $this->arrPersonInfo = $arrPerson;
//            }
//        }
//        else
//        {
//            //app 版本的登录   先预留
//        }
//        return parent::beforeAction($action);
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
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['POST', 'GET', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['X-Wsse', 'Origin', 'X-Requested-With', 'Content-Type', 'Accept'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age' => 3600,
                ],
            ]
        ];
    }
    
    protected $type = [
    					1 => '报销',
    					2 => '借款',
    					3 => '还款',
    ];

    public static $code = [
        200 => '成功',
        400 => '失败',
        401 => '未登录',
        402 => '用户不存在',
        403 => '参数错误',
        404 => '系统错误',
        1010 => '申请ID不能确认，请求不合法'
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