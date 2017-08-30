<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/8/30
 * Time: 17:44
 */

namespace app\commands;


use app\modules\oa_v1\logic\TreeTagLogic;
use yii\console\Controller;

/**
 * 财务系统标签
 * 
 * Class TreeTagController
 * @package app\commands
 */
class TreeTagController extends Controller
{
    public function actionIndex()
    {
        TreeTagLogic::instance()->getTreeTag();
    }
}