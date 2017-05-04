<?php

namespace app\modules\oa_v1\controllers;


/**
 * Default controller for the `oa_v1` module
 */
class DefaultController extends BaseController
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->_return(null);
    }
}
