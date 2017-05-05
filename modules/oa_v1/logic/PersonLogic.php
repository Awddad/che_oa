<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/5
 * Time: 11:26
 */

namespace app\logic;


use app\models\Person;

/**
 * 人员相关逻辑
 *
 * Class PersonLogic
 * @package app\logic
 */
class PersonLogic extends Logic
{
    /**
     * 根据ID得到用户姓名
     *
     * @param $personId
     * @return string
     */
    public function getPersonName($personId)
    {
        return Person::findOne($personId)->person_name;
    }
}