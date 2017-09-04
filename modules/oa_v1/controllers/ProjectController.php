<?php
/**
 * Created by PhpStorm.
 * User: YJR
 * Date: 2017/9/4
 * Time: 16:00
 */

namespace app\modules\oa_v1\controllers;


use app\modules\oa_v1\logic\ProjectLogic;

class ProjectController extends BaseController
{
    public function actionGetProjects()
    {
        $projects = ProjectLogic::instance()->getProjects();
        return $this->_return($projects);
    }
}