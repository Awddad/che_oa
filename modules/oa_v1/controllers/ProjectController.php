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
        $org_id = \Yii::$app->request->get('org_id',0);
        $projects = ProjectLogic::instance()->getProjects($org_id);
        return $this->_return($projects);
    }
}