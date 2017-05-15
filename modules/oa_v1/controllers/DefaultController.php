<?php

namespace app\modules\oa_v1\controllers;

use Yii;
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
        $data = TreeTagLogic::instance()->getTreeTagsByParentId(1);
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
    
    /**
     * 获取用户信息接口
     * @return type
     */
    public function actionGetUserInfo()
    {
        $this->arrPersonInfo;
        $arrData = [
            'userinfo' => $this->arrPersonInfo->toArray()
        ];
        return $this->_return($arrData);
    }
    
    /**
     * 获取申请id
     * @return type
     */
    public function actionGetApplyId()
    {
        $intType = intval(Yii::$app->request->get('type'));
        if(array_key_exists($intType, $this->type))
        {
            switch($intType)
            {
                case 1: 
                    $id = date('YmdHis') . '01' . rand(100, 999);
                    break;
                case 2: 
                    $id = date('YmdHis') . '02' . rand(100, 999);
                    break;
                case 3: 
                    $id = date('YmdHis') . '03' . rand(100, 999);
                    break;
            }
            return $this->_return(['apply_id' => $id]);
        }
        else
        {
            return $this->_return([], 403);
        }
    }

    public function actionOrg()
    {
        $data = PersonLogic::instance()->getOrgs();
        return $this->_return($data);
    }
}
