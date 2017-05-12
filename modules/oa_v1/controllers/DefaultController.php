<?php

namespace app\modules\oa_v1\controllers;


use app\modules\oa_v1\logic\PersonLogic;
use app\modules\oa_v1\logic\TreeTagLogic;


/**
 * Default controller for the `oa_v1` module
 */
class DefaultController extends BaseController
{
    /**
     * TEST
     *
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $data = TreeTagLogic::instance()->getTreeTagsByParentId();
        return $this->_return($data);
    }

    /**
     * 报销
     */
    public function actionGetPerson()
    {
        $person = PersonLogic::instance()->getSelectPerson();
        return $this->_return($person);
    }
}
