<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/4
 * Time: 9:45
 */

namespace app\modules\oa_v1\controllers;


use app\models\Menu;
use app\models\User;
use app\modules\oa_v1\logic\PersonLogic;
use yii\filters\auth\CompositeAuth;
use yii\filters\ContentNegotiator;
use yii\filters\RateLimiter;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\HttpException;
use yii\web\Response;
use Yii;
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
    /**
     * @var  array |User $arrPersonInfo
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
     *
     * @throws HttpException
     */
    public function beforeAction($action)
    {
        //app下载特殊处理
        if(Yii::$app->request->get('type') == 'crm' && Yii::$app->controller->id == 'default'
            && in_array(Yii::$app->controller->action->id, ['get-pdf', 'down'])) {
            $time = Yii::$app->request->get('time');
            $sign = Yii::$app->request->get('sign');
            $applyId= Yii::$app->request->get('apply_id');
            $md5 = md5($applyId.$time.'crm');
            if($sign == $md5) {
                return true;
            }
        }
        $session = Yii::$app->session;
        $objPerson = $session->get('USER_INFO');
        $intRoleId = $session->get('ROLE_ID');
        if(empty($objPerson) || !$intRoleId) {
            $loginUrl = Yii::$app->params['quan_xian']['auth_sso_login_url'];
            header("Content-type: application/json");
            echo json_encode($this->_return(['login_url' => $loginUrl], 401));
            die();
        }
        $this->arrPersonInfo = $objPerson;

        $this->setUserRoleInfo($intRoleId);

        //权限管理
        $roleInfo = Role::findOne($this->roleId);
        $roleArr = ArrayHelper::getColumn(json_decode($roleInfo->permissions), 'url');
        $requestUrlArr = explode('?', $_SERVER['REQUEST_URI']);
        $allMenu = ArrayHelper::getColumn(Menu::find()->asArray()->all(), 'url');

        if ($action->id == 'index') {
            $url_one = '/' . $action->controller->id;
            if (!in_array($url_one, $roleArr) && !in_array($url_one, $roleArr) && in_array($requestUrlArr, $allMenu)) {
                throw new HttpException(403);
            }
        } else {
            if (!in_array($requestUrlArr, $roleArr) && in_array($requestUrlArr, $allMenu)) {
                throw new HttpException(403);
            }
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
//        if($blnForce == false)//不强制刷新的时候 从缓存中获取
//        {
//            $this->arrPersonRoleInfo = \Yii::$app->cache->get($strCacheKey);
//        }
//
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
                    $org = PersonLogic::instance()->getCompanyOrgIds($this->arrPersonInfo);
                    $orgIds = ArrayHelper::merge($org, explode(',', $objRoleOrgMod->org_ids));
                    $this->arrPersonRoleInfo['permissionOrgIds'] = $orgIds;
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
                    'Access-Control-Request-Method' => ['POST', 'GET'],
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
                        7 => '用章',
    					8 => '固定资产领用',
    					9 => '固定资产归还',
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
        405 => '没有申请权限',
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
        //$message = (!$message) ? static::$code[$code] : $message;
        $message = static::$code[$code] ?  : $message;

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