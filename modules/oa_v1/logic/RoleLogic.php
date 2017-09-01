<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/8/31
 * Time: 17:16
 */

namespace app\modules\oa_v1\logic;


use app\logic\Logic;
use app\models\Menu;
use app\models\Role;

/**
 * 权限相关
 *
 * Class RoleLogic
 * @package app\modules\oa_v1\logic
 */
class RoleLogic extends Logic
{
    /**
     * 获取role
     *
     * @param $roleId
     *
     * @return mixed|static
     */
    public function getRole($roleId)
    {
        $key = 'OA_ROLE_'.$roleId;
        $cache = \Yii::$app->cache;
        $role = $cache->get($key);
        if (empty($role)) {
            $role = Role::findOne($roleId);
            $cache->set($key, $role);
        }
        return $role;
        
    }
    
    /**
     * 获取系统菜单
     *
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    public function getMenu()
    {
        $key = 'OA_ROLE_MENU';
        $cache = \Yii::$app->cache;
        $menu = $cache->get($key);
        if (empty($menu)) {
            $menu = Menu::find()->all();
            $cache->set($key, $menu);
        }
        return $menu;
    }
}