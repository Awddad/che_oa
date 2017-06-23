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
use yii\helpers\ArrayHelper;

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
     *
     * @param $personId
     *
     * @return array
     */
    public function getSelectPerson($personId)
    {
        $persons = Person::find()->where(['!=', 'person_id', $personId])->orderBy('person_id desc')->all();
        $data = [];
        foreach ($persons as $person) {
        	if($person->org_id <= 0){
        		continue;
        	}
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
     * @return array
     */
    public function getOrgName($person)
    {
        $org = Org::findOne($person->org_id);
        if($org->pid == 0) {
            return [$org->org_short_name] ?  : [$org->org_name];
        }
        $orgArr =  $this->getParentOrg($org);
        $orgArr[] = $org->org_short_name ? $org->org_short_name : $org->org_name;
        return $orgArr;
        
    }

    /**
     * @param $org
     * @param $result
     * @return array
     */
    public function getParentOrg($org, &$result = [])
    {
        $parent = Org::findOne($org->pid);
        if($parent->pid != 0 ){
            $this->getParentOrg($parent, $result);
            $result[] = $parent->org_short_name ? : $parent->org_name;
        }
        return $result;
    }


    /**
     * 通过关键字，获取员工id
     * @param $key
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getPersonIdsByKey($key)
    {
        return Person::find()->select('person_id')->where(['like', 'person_name', $key])->all();
    }

    /**
     * 通过部门id，获取部门员工
     * @param $orgId
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getPersonIdsByOrgId($orgId)
    {
        return Person::find()->select('person_id')->where(['org_id' => $orgId])->all();
    }

    /**
     * 组织架构
     * @return array
     */
    public function getOrg()
    {

        $org = Org::find()->all();
        return ArrayHelper::map($org, 'org_id', 'org_name');
    }
    /**
     * 通过用户id 获得部门
     * @param int $person_id
     * @return string
     */
    public function getOrgNameByPersonId($person_id)
    {
    	$person = Person::findOne($person_id);
    	$orgArr = $this -> getOrgName($person);
    	return implode('-', $orgArr);
    }

    /**
     * @param int $orgId
     * @param $data
     * @return array
     */
    public function getOrgs($orgId = 0, $data = [])
    {
        $org = Org::find()->where(['pid' => $orgId])->all();
        if(empty($org)) {
            return [];
        }
        foreach ($org as $value) {
            if ($children = $this->getOrgs($value->org_id, [])) {
                $data[] = [
                    'label' => $value->org_name,
                    'value' => $value->org_id,
                    'children' => $this->getOrgs($value->org_id, [])
                ];
            } else {
                $data[] = [
                    'label' => $value->org_name,
                    'value' => $value->org_id,
                ];
            }
        }
        return $data;
    }
    
    /**
     * 根据部门id 获得部门名称
     * @param unknown $org_id
     */
    public function getOrgById($org_id)
    {
    	$org = Org::findOne($org_id);
    	if($org->pid == 0) {
    		return $org->org_short_name ?  : $org->org_name;
    	}
    	$orgArr =  $this->getParentOrg($org);
    	$orgArr[] = $org->org_short_name ? $org->org_short_name : $org->org_name;
    	return implode('-', $orgArr);
    }
}