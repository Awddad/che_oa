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
use app\models\RoleOrgPermission;
use yii\helpers\ArrayHelper;
use yii;

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
     * @param Person $person
     *
     * @return array
     */
    public function getSelectPerson($person)
    {
        $companyArr = $this->getCompanyIds($person);
        if ($person->company_id == 1) {
            $persons = Person::find()->where([
                '!=', 'person_id', $person->person_id
            ])->andWhere(['is_delete'=>0])->orderBy('person_id desc')->all();
        } else {
            $persons = Person::find()->where(['is_delete' => 0])->andWhere([
                'or',
                ['in', 'company_id', [1, $person->company_id]],
                ['in', 'person_id', $companyArr]
            ])->andWhere([
                '!=', 'person_id', $person->person_id
            ])->orderBy('person_id desc')->all();
        }
    
        $data = [];
        /**
         * @var Person $v
         */
        foreach ($persons as $v) {
            if($v->org_id <= 0){
                continue;
            }
            $personName = $v->person_name. ' '. $v->org_full_name;
            $data[] = [
                'id' => $v->person_id,
                'name' => $personName
            ];
        }
        
        $caiwu = $this->getCaiwu($person->person_id);
    
        foreach ($caiwu as $v) {
            $personName = $v['person_name']. ' '. $v['org_full_name'];
            $data[] = [
                'id' => $v['person_id'],
                'name' => $personName
            ];
        }
        $data = ArrayHelper::index($data,'id');
        sort($data);
        return $data;
    }
    
    /**
     * @param int $personId
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getCaiwu($personId)
    {
        $data = Person::find()->select([
            'oa_person.person_id',
            'oa_person.person_name',
            'oa_person.org_full_name'
        ])->innerJoin('oa_role', 'oa_role.id = oa_person.role_ids')->where([
            'and',
            ['in', 'oa_role.slug', ['caiwu', 'caiwujingli']],
            ['!=', 'oa_person.person_id', $personId]
        ])->asArray()->all();
        return $data;
    }
    
    /**
     * 获取员工所在的公司
     *
     * @param Person $person
     * @return array
     */
    public function getCompanyIds($person)
    {
        /**
         * @var RoleOrgPermission $roleOrgPermission
         */
        $roleOrgPermission = RoleOrgPermission::find()->where(
            'FIND_IN_SET('.$person->company_id.',company_ids)'
        )->asArray()->all();
        if (!empty($roleOrgPermission))
        {
            return ArrayHelper::getColumn($roleOrgPermission, 'person_id');
        }
        //$companyArr[] = 1;
        return [];
    }
    
    /**
     * @param Person $person
     * @return array
     */
    public function getOrgName($person)
    {
        $org = Org::findOne($person->org_id);
        if (empty($org)) {
            return ['未找到组织架构'];
        }
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
    	return $person->org_full_name;
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
    
    /**
     * 获取公司组织架构ID
     *
     * @param Person $person
     *
     * @return $data
     */
    public function getCompanyOrgIds($person)
    {
        $companies = Person::find()->select('org_id')->where([
            'person_id' => $person->person_id
        ])->all();
        return ArrayHelper::getColumn($companies, 'org_id');
    }
    
    /**
     * 财务确认对应公司
     *
     * @param array $companyIds
     * @return array
     */
    public function getSelectOrg($companyIds)
    {
        $org = Org::find()->where(['in', 'org_id', $companyIds])->all();
        $data = [];
        if(!empty($org)) {
            foreach ($org as $value) {
                if ($children = $this->getChildrenOrg($value->org_id)) {
                    $data[] = [
                        'label' => $value->org_name,
                        'value' => $value->org_id,
                        'children' => $children
                    ];
                } else {
                    $data[] = [
                        'label' => $value->org_name,
                        'value' => $value->org_id,
                    ];
                }
            }
        }
        return $data;
    }
    
    /**
     * 公司下的组织
     *
     * @param $orgId
     *
     * @return array
     */
    public function getChildrenOrg($orgId)
    {
        $data = [];
        $org = Org::find()->where(['pid' => $orgId])->all();
        if(empty($org)) {
            return [];
        }
        foreach ($org as $value) {
            if ($children = $this->getChildrenOrg($value->org_id)) {
                $data[] = [
                    'label' => $value->org_name,
                    'value' => $value->org_id,
                    'children' => $children
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
     * 获得组织架构用户
     */
    public function getOrgPerson()
    {
        $key = 'org_person';
        $cache = yii::$app->cache;
        if(!$data = $cache->get($key)){
            $data = $this->_getOrgPerson(1);
            $cache->set($key, $data,3600);
        }
        return $data;
    }
    
    protected function _getOrgPerson($pid)
    {
        $data = [];
        $org = Org::findAll(['pid'=>$pid]);
        if($org){
            $tmp = [];
            foreach($org as $v){
                $tmp =[
                    'id' => $v->org_id,
                    'label' => $v->org_name,
                    'is_user' => false,
                ];
                $tmp_child = $this->_getOrgPerson($v->org_id);
                $tmp_child && $tmp['children'] = $tmp_child;
                
                $person = Person::findAll(['org_id'=>$v->org_id,'is_delete'=>0]);
                if($person){
                    foreach($person as $vv){
                        $tmp['children'][] = [
                            'id' => $vv->person_id,
                            'label' => $vv->person_name,
                            'is_user' => true,
                        ];
                    }
                }
                $tmp && $data[] = $tmp;
            }
        }
        return $data;
    }
    
    
}