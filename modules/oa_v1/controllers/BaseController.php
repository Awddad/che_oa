<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/4
 * Time: 9:45
 */

namespace app\modules\oa_v1\controllers;


use app\models\Menu;
use app\models\Person;
use app\models\User;
use app\modules\oa_v1\logic\PersonLogic;
use app\modules\oa_v1\logic\RoleLogic;
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
     * @var  Person $arrPersonInfo
     */
    public $arrPersonInfo = [];//用户登录信息保存
    
    public $arrPersonRoleInfo = [];//用户的角色和权限信息 - 菜单权限 - 数据权限
    
    public $roleId ;//用户的角色 数据权限
    
    public $roleName ;//用户的角色别名
    
    /**
     * 用户对应的公司
     * @var array
     */
    public $companyIds = [];
    
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

        $this->setUserRoleInfo($intRoleId, $action);
        
        return parent::beforeAction($action);
    }
    
    /**
     * 初始化登录用户的权限信息 包含目录权限和数据（组织架构）权限
     * @param $intRoleId
     * @param $action
     * @param string $strOs
     *
     * @return bool
     * @throws HttpException
     */
    protected function setUserRoleInfo($intRoleId, $action, $strOs = 'web')
    {

        $this->roleId = $intRoleId;
        $personId = $this->arrPersonInfo->person_id;


        $objRoleMod = RoleLogic::instance()->getRole($intRoleId);
        if($objRoleMod)
        {
            $this->roleName = $objRoleMod->slug;
            //目录权限
            $arrMenuTmp = ArrayHelper::getColumn(json_decode($objRoleMod->permissions, true), 'slug');
            //去重
            $this->arrPersonRoleInfo['roleInfo'] = array_unique($arrMenuTmp);
            //数据权限
            $objRoleOrgMod = RoleOrgPermission::find()->where([
                'person_id' => $personId,
                'role_id' => $intRoleId
            ])->one();
            if($objRoleOrgMod && $objRoleOrgMod->org_ids)//设置过数据权限
            {
                $org = PersonLogic::instance()->getCompanyOrgIds($this->arrPersonInfo);
                $orgIds = ArrayHelper::merge($org, explode(',', $objRoleOrgMod->org_ids));
                $this->arrPersonRoleInfo['permissionOrgIds'] = $orgIds;
                $this->companyIds = explode(',', $objRoleOrgMod->company_ids);
            } else {
                $org = PersonLogic::instance()->getCompanyOrgIds($this->arrPersonInfo);
                $this->arrPersonRoleInfo['permissionOrgIds'] = $org;
                $this->companyIds = [$this->arrPersonInfo->company_id];
            }
            
            // 判断接口权限
            $roleArr = ArrayHelper::getColumn(json_decode($objRoleMod->permissions), 'url');
            $requestUrlArr = explode('?', $_SERVER['REQUEST_URI']);
            $allMenu = ArrayHelper::getColumn(RoleLogic::instance()->getMenu(), 'url');
    
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
        }
 
        return true;
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
    
    /**
     * 错误信息
     *
     * @var array
     */
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
        2405 => '未找到阎行卡信息',
        2406 => '非法操作',
        2407 => '驳回失败',
        2408 => '未找到申请单',
        2409 => '银行卡已存在',
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