<?php
namespace app\modules\third_api\controllers;
/**
 * @功能：与权限系统交互的功能 - 权限系统数据变更通知接受
 * @作者：王雕
 * @创建时间：2017-05-17
 */
use app\modules\oa_v1\logic\TreeTagLogic;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\ContentNegotiator;
use yii\filters\RateLimiter;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use app\logic\server\QuanXianServer;

class QuanxianController extends Controller
{
    /**
     * @功能：权限系统通知入口
     * @作者：王雕
     * @创建时间：2017-05-17
     */
    public function actionIndex()
    {
        $strType = Yii::$app->request->get('api');//测试可以手动拉取
        if(empty($strType))
        {
            $strType = Yii::$app->request->post('api');//请求接口的时候告知变动类型是什么
        }
        
        $objQx = new QuanXianServer();
        $intResult = 0;
        switch($strType)
        {
            //组织架构相关变动
            case 'organizations/tree'://组织架构树形结构
            case 'organizations/types'://组织架构类型
                $intResult += $objQx->curlUpdateOrg();
                break;
            case 'organizations/positions'://职位
            	$intResult += $objQx->curlUpdatePositions();
            	break;
            case 'users'://用户列表
                $intResult += $objQx->curlUpdateAllUser();
                //break;
            case 'projects/users'://项目用户
                $intResult += $objQx->curlUpdateUser();
                break;
            
            //角色权限目录相关信息变动
            case 'projects/roles'://项目角色
                $intResult += $objQx->curlUpdateRole();//角色信息
                break;
            case 'projects/permission-tree'://项目菜单权限树形结构
            case 'projects/permissions'://项目菜单权限树形结构
                $intResult += $objQx->curlUpdateMenus();//项目菜单
                break;
            case 'projects/role_user':
                $intResult += $objQx->curlUpdateUserRoleOrgPermission();//用户的数据权限
                break;
            //菜单信息变动
        }
        if ($intResult == 0)
            echo '更新成功';
        else
            echo '更新成功，影响数据条数:' . $intResult;
        die();
    }

    /**
     * @功能：手动同步权限系统(测试使用)
     * @作者：yjr
     * @创建时间：2017-09-11
     */
    public function actionSync()
    {
        $objQx = new QuanXianServer();
        $intResult = 0;
        $intResult += $objQx->curlUpdateOrg();
        $intResult += $objQx->curlUpdatePositions();
        $intResult += $objQx->curlUpdateAllUser();
        $intResult += $objQx->curlUpdateUser();
        $intResult += $objQx->curlUpdateRole();//角色信息
        $intResult += $objQx->curlUpdateMenus();//项目菜单
        $intResult += $objQx->curlUpdateUserRoleOrgPermission();//用户的数据权限
        if ($intResult == 0)
            echo '更新成功';
        else
            echo '更新成功，影响数据条数:' . $intResult;
        die();
    }
}
