<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/10
 * Time: 18:40
 */

namespace app\modules\oa_v1\logic;


use app\logic\Logic;
use app\logic\server\ThirdServer;
use app\models\TagTree;

/**
 * 科目标签树形结构
 *
 * Class TreeTagLogic
 * @package app\modules\oa_v1\logic
 */
class TreeTagLogic extends Logic
{
    /**
     * 获取tree_tag
     */
    public function getTreeTag()
    {
        $treeTags = ThirdServer::instance([
            'token' => \Yii::$app->params['cai_wu']['token'],
            'baseUrl' => \Yii::$app->params['cai_wu']['baseUrl']
        ])->getTagTree();
        if($treeTags && $treeTags['success'] == 1) {
            $data = $this->getSaveArr($treeTags['data']);
            return $this->saveTreeTag($data);
        }
        return false;
    }

    /**
     * 批量插入
     *
     * @param $data
     * @return int
     */
    public function saveTreeTag($data)
    {
        return \Yii::$app->db->createCommand()->batchInsert('oa_tag_tree',
            ['id', 'parent_id', 'name', 'type', 'level' ,'status'],
            $data
        )->execute();
    }

    /**
     * 获取批量保存数组
     *
     * @param $treeTag
     * @param array $returnData
     * @return array
     */
    public function getSaveArr($treeTag, &$returnData = [])
    {
        foreach ($treeTag as $v) {
            $returnData[] = [
                $v['id'], $v['parent_id'], $v['name'] , $v['type'], $v['level'] , $v['status']
            ];
            if(!empty($v['children'])) {
                $this->getSaveArr($v['children'], $returnData);
            }
        }
        return $returnData;
    }

    /**
     * 获取报销类别
     *
     *
     * @param int $parentId 17-费用支出 | 1-收入 | 2-支出
     * @param array $data
     * @return array
     */
    public function getTreeTagsByParentId($parentId = 17, $data =[])
    {
        $tagTree = TagTree::find()->where([
            'parent_id' => $parentId,
            'status' => 1
        ])->orderBy(['id' => SORT_ASC])->all();
        if(empty($tagTree)) {
            return [];
        }
        foreach ($tagTree as $tree) {
            $data[] = [
                'label' => $tree->name,
                'value' => $tree->id,
                'children' => $this->getTreeTagsByParentId($tree->id, [])
            ];
        }
        return $data;
    }


}