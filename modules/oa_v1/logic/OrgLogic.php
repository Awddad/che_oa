<?php
namespace app\modules\oa_v1\logic;

use app\models\Org;
use yii\helpers\ArrayHelper;

class OrgLogic extends BaseLogic
{
    /**
     * 获得组织架构树形图
     * @param int $pid
     */
    public function getOrgTree($pid = 0)
    {
        $res = Org::findAll(['pid' => $pid]);
        $data = [];
        foreach($res as $v){
            $tmp = [
                'id' => $v['org_id'],
                'label' => $v['org_short_name'] ?: $v['org_name'], 
            ];
            $tmp_child = $this->getOrgTree($v['org_id']);
            $tmp_child && $tmp['children'] = $tmp_child;
            $data[] = $tmp;
        }
        return $data;
    }
    
    /**
     * 获得所有子组织
     * @param unknown $pid
     */
    public function getAllChildID($pid = 0)
    {
        $data = [$pid];
        $res = Org::find()->where(['pid' => $pid])->asArray()->all();;
        $data = ArrayHelper::merge($data, array_column($res,'org_id'));
        foreach($res as $v){
            $tmp = $this->getAllChildID($v['org_id']);
            $data = ArrayHelper::merge($data, $tmp);
        }
        return $data;
    }
}