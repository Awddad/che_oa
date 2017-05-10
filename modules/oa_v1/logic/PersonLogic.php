<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/5
 * Time: 11:26
 */

namespace app\modules\oa_v1\logic;

use app\models\Org;
use app\models\Person;

/**
 * 人员相关逻辑
 *
 * Class PersonLogic
 * @package app\logic
 */
class PersonLogic extends BaseLogic
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

    /**
     * 获取筛选
     */
    public function getSelectPerson()
    {
        $persons = Person::find()->orderBy('person_id desc')->all();
        $data = [];
        foreach ($persons as $person) {
            $orgArr = $this->getOrgName($person);
            $personName = $person->person_name. ' '. implode('-', $orgArr);
            $data[] = [
                'id' => $person->person_id,
                'name' => $personName
            ];
        }
        return $data;
    }

    /**
     * @param Person $person
     * @return string
     */
    public function getOrgName($person)
    {
        $org = Org::findOne($person->org_id);
        if($org->pid == 0) {
            return [$org->org_name];
        } else {
            $arr = [$org->org_name];
            $orgArr =  $this->getParentOrg($org, $arr);
            rsort($orgArr);
            return $orgArr;
        }
    }

    /**
     * @param $org
     * @param $result
     * @return string
     */
    public function getParentOrg($org, &$result)
    {
        $parent = Org::findOne($org->pid);
        $result[] = $parent->org_name;
        if($parent->pid != 0 ){
            $this->getParentOrg($parent, $result);
        }
        return $result;
    }
}