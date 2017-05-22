<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/4
 * Time: 9:45
 */

namespace app\modules\oa_v1\controllers;


use app\models\Menu;
use yii\filters\auth\CompositeAuth;
use yii\filters\ContentNegotiator;
use yii\filters\RateLimiter;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\Response;
use Jasny\SSO\Broker;
use Yii;
use app\models\Person;
use app\models\Org;
use app\models\Role;
use app\models\RoleOrgPermission;

/**
 * 接口基础
 *
 * Class BaseController
 * @package app\modules\oa_v1\controllers
 */
class BaseController extends Controller
{
    public $arrPersonInfo = [];//用户登录信息保存
    
    public $arrPersonRoleInfo = [];//用户的角色和权限信息 - 菜单权限 - 数据权限
    
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
    public function beforeAction($action)
    {
        $strOsType = \Yii::$app->session->get('os_type', 'web');//默认是web版的
        if($strOsType == 'web')//web版的使用单点登录
        {
            $serverUrl = Yii::$app->params['quan_xian']['auth_sso_url'];//单点登录地址
            $brokerId = Yii::$app->params['quan_xian']['auth_broker_id'];//项目appID
            $brokerSecret = Yii::$app->params['quan_xian']['auth_broker_secret'];//配置的项目 Secret
            $loginUrl = Yii::$app->params['quan_xian']['auth_sso_login_url'];
            $broker = new Broker($serverUrl, $brokerId, $brokerSecret);
            $broker->attach(true);
            $user = $broker->getUserInfo();//获取用户信息，这里会curl单点登录获取用户信息,但是不全
            if(!$user)
            {
                //用户没有登录 需要跳转到登录页面去登录
                $broker->clearToken();
                header("Content-type: application/json");
                echo json_encode($this->_return(['login_url' => $loginUrl], 401));
                die();
            }
            else
            {
                $strCacheKey = 'login_' . $strOsType . '_' . $broker->token;
                $objPerson = \Yii::$app->cache->get($strCacheKey);
                if(empty($objPerson) || $objPerson->person_id != $user['id'])
                {
                    $objPerson = Person::findOne(['person_id' => $user['id']]);
                    if(!$objPerson)
                    {
                        //用户信息取不到的时候提示用户不存在
                        header("Content-type: application/json");
                        echo json_encode($this->_return(['login_url' => $loginUrl], 402));
                        die();
                    }
                    else
                    {
                        Yii::$app->cache->set($strCacheKey, $objPerson);
                    }
                }
                $this->arrPersonInfo = $objPerson;
                
                //如果没选角色，默认一个角色
                if(empty($this->arrPersonRoleInfo['roleInfo']) || $this->arrPersonRoleInfo['permissionOrgIds'] ) {
                    $arrRoleIds = explode(',', $this->arrPersonInfo->role_ids);
                    if(empty($intRoleId) && count($arrRoleIds) >= 1)
                    {
                        $intRoleId = $arrRoleIds[0];
                    }
                    $this->setUserRoleInfo($intRoleId);
                }
                
                //设置角色信息
                $session = Yii::$app->getSession();
                if(isset($session['role_id']))
                {
                    $this->setUserRoleInfo($session['role_id']);
                }
            }
        }
        else
        {
            //app 版本的登录   先预留
        }
        return parent::beforeAction($action);
    }
    
    /**
     * @功能：              初始化登录用户的权限信息 包含目录权限和数据（组织架构）权限
     * @作者：              王雕
     * @创建时间：          2017-05-15
     * @param int           $intRoleId      角色id
     * @param string        $strOs          平台 web / Android 。。。。
     * @param boolen        $blnForce       是否强刷权限信息（不使用原缓存）
     * @return boolean      $result         true - 设置成功 / 设置失败
     */
    protected function setUserRoleInfo($intRoleId, $strOs = 'web', $blnForce = false)
    {

        $result = false;
        $personId = $this->arrPersonInfo->person_id;
        $strCacheKey = 'role_info_' . $strOs . '_' . $intRoleId . '_' . $personId;
        if($blnForce == false)//不强制刷新的时候 从缓存中获取
        {
            //$this->arrPersonRoleInfo = \Yii::$app->cache->get($strCacheKey);
        }
        
        if(empty($this->arrPersonRoleInfo))
        {
            $objRoleMod = Role::findOne(['id' => $intRoleId]);
            if($objRoleMod)
            {
                //目录权限
                $arrMenuTmp = ArrayHelper::getColumn(json_decode($objRoleMod->permissions, true), 'slug');
                //去重
                $this->arrPersonRoleInfo['roleInfo'] = array_unique($arrMenuTmp);
                //数据权限
                $objRoleOrgMod = RoleOrgPermission::findOne(['person_id' => $personId, 'role_id' => $intRoleId]);
                if($objRoleOrgMod)//设置过数据权限
                {
                    $this->arrPersonRoleInfo['permissionOrgIds'] = explode(',', $objRoleOrgMod->org_ids);
                }
                $result = true;//取库获取到数据了
                \Yii::$app->cache->set($strCacheKey, $this->arrPersonRoleInfo);
            }
        }
        else
        {
            $result = true;//获取缓存了
        }
        return $result;
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
        1010 => '申请ID不能确认，请求不合法',
        1011 => '图片上传失败',
        1012 => '文件上传失败',
        2001 => '当前状态，无法执行该操作',
        2002 => '审批人错误',
        2101 => '您无权撤销该申请',
        2404 => '状态异常，请联系管理员',
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

    /**
     * 错误信息返回
     * @param int $code
     * @param string|array|object $data
     * @param string $message
     * @return array
     */
    public function _returnError($code, $data = null, $message = 'fail')
    {
        return $this->_return($data, $code, $message);
    }
}