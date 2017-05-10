<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/9
 * Time: 16:34
 */

namespace app\modules\oa_v1\logic;


use app\models\Apply;
use yii\data\Pagination;

/**
 * 付款相关逻辑
 *
 * Class PayLogic
 * @package app\modules\oa_v1\logic
 */
class PayLogic extends BaseLogic
{
    /**
     * 待确认付款列表
     */
    public function canConfirmList()
    {
        $type = \Yii::$app->request->post('type');

        $query = Apply::find()->where([
            'status' => 4
        ]);
        //筛选
        if($type && in_array($type, [1,2])) {
            $query->andWhere([
                'type' => $type
            ]);
        } else {
            $query->andWhere([
                'in', 'type', [1, 2]
            ]);
        }

        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount]);

        //当前页
        $currentPage = \Yii::$app->request->post('currentPage') ? : 1;
        //每页显示条数
        $perPage = \Yii::$app->request->post('perPage') ? : 20;

        $pagination->setPageSize($perPage, true);

        $pagination->setPage($currentPage -1);
        $models = $query->limit($pagination->getLimit())->offset(
            $pagination->getPage() * $pagination->pageSize
        )->orderBy(['create_time' => SORT_DESC])->all();
        $data   = [];
        if(!empty($models)) {
            foreach ($models as $model) {
                if ($model->type == 1) {
                    $typeName = '申请报销';
                    $money = $model->expense->money;
                } else {
                    $typeName = '申请借款';
                    $money = $model->loan->money;
                }

                $data[] = [
                    'create_time' => date('Y-m-d H:i'),
                    'type_name' => $typeName,
                    'apply_id' => $model->apply_id,
                    'title' => $model->title,
                    'money' => $money
                ];
            }
            return [
                'data' => $data,
                'pages' => $this->pageFix($pagination)
            ];
        }
        return $data;
    }
}