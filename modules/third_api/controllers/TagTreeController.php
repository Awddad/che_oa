<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/5/31
 * Time: 14:00
 */

namespace app\modules\third_api\controllers;


use app\modules\oa_v1\logic\TreeTagLogic;
use yii\rest\Controller;

/**
 * 更新Tag_Tree
 *
 * Class TagTreeController
 * @package app\modules\third_api\controllers
 */

class TagTreeController extends Controller
{
    public function actionIndex()
    {
        $result = TreeTagLogic::instance()->getTreeTag();
        if($result) {
            return ['message' => '更新成功', 'code' => 200];
        }
        return ['message' =>'更新失败', 'code' => 400];
    }
}