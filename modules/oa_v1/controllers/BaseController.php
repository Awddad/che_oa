<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/4
 * Time: 9:45
 */

namespace app\modules\oa_v1\controllers;


use app\models\User;
use yii\filters\auth\CompositeAuth;
use yii\filters\ContentNegotiator;
use yii\filters\RateLimiter;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\Response;
use Yii;
use app\models\Role;
use app\models\RoleOrgPermission;
use app\models\Person;

/**
 * 接口基础
 *
 * Class BaseController
 * @package app\modules\oa_v1\controllers
 */
class BaseController extends Controller
{
    /**
     * @var User $arrPersonInfo
     */
    public $arrPersonInfo = [];//用户登录信息保存
    
    public $arrPersonRoleInfo = [];//用户的角色和权限信息 - 菜单权限 - 数据权限
    
    public $roleId ;//用户的角色 数据权限
    
    /**
     *不做登录校验的请求的白名单 controller/action格式
     * @var array 
     * Yii::$app->controller->module->id    模块名称
     * Yii::$app->controller->id            控制器名称
     * Yii::$app->controller->action->id    方法名称
     */
    protected static $arrWhiteList  = [
        '/default/get-user-info',
        '/oa_v1/apply/get-bankcard',
        '/oa_v1/apply/get-user-list',
        '/oa_v1/apply/get-type',
        '/oa_v1/apply/add-bankcard',
        '/oa_v1/pay-confirm/form',
        '/oa_v1/back-confirm/form',
        '/oa_v1/back/can-back',
        '/oa_v1/pay-confirm/export',
        '/oa_v1/back-confirm/export',
        '/oa_v1/approval-log/update',
        '/oa_v1/apply/get-baoxiao',
        '/oa_v1/apply/get-jiekuan',
        '/oa_v1/apply/get-payback',
        '/oa_v1/jiekuan/export'
    ];




    /**
     * @功能：项目入口，判断用户登录信息
     * @作者：王雕
     * @创建时间：2017-05-04
     * @param $action
     * @return boolean
     */
    public function beforeAction($action)
    {
        $strOsType = \Yii::$app->session->get('os_type', 'web');//默认是web版的
        if($strOsType == 'web')//web版的使用单点登录
        {
            if(true){//建华test
            	$objPerson = Person::findOne(['person_id' => 272]);
            	$arrRoleIds = explode(',', $objPerson->role_ids);
            	
            	$intRoleId = $arrRoleIds[0];
            }else{
            	$session = Yii::$app->session;
            	$objPerson = $session->get('USER_INFO');
            	$intRoleId = $session->get('ROLE_ID');
            }
            if(empty($objPerson) || !$intRoleId) {
                $loginUrl = Yii::$app->params['quan_xian']['auth_sso_login_url'];
                header("Content-type: application/json");
                echo json_encode($this->_return(['login_url' => $loginUrl], 401));
                die();
            }
            $this->arrPersonInfo = $objPerson;
    
            $this->setUserRoleInfo($intRoleId);

            //权限
//            $roleInfo = Role::findOne($this->roleId);
//            $roleArr = ArrayHelper::getColumn(json_decode($roleInfo->permissions), 'url');
//            $requestUrlArr = explode('?', $_SERVER['REQUEST_URI']);
//            if (!in_array($requestUrlArr['0'], static::$arrWhiteList)
//                && !in_array(Yii::$app->controller->id, ['default', 'upload'])
//            ) {
//                if (!in_array($requestUrlArr['0'], $roleArr)) {
//                    header("Content-type: application/json");
//                    echo json_encode($this->_return([], 403, '您无操作权限，请联系管理员'));
//                    die();
//                }
//            }
        } else {
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
        $this->roleId = $intRoleId;
        $personId = $this->arrPersonInfo->person_id;
        $strCacheKey = 'role_info_' . $strOs . '_' . $intRoleId . '_' . $personId;
        if($blnForce == false)//不强制刷新的时候 从缓存中获取
        {
            $this->arrPersonRoleInfo = \Yii::$app->cache->get($strCacheKey);
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
    					4 => '付款',
    					5 => '请购',
    					6 => '需求',
    					7 => '固定资产领用',
    					8 => '固定资产归还',
    					9 => '用章',
    					10 => '转正',
    					11 => '离职',
    					12 => '调职',
    					13 => '开店',
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
        $message = (!$message) ? static::$code[$code] : $message;
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