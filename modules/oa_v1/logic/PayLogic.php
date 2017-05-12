<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/9
 * Time: 16:34
 */

namespace app\modules\oa_v1\logic;


use app\logic\server\ThirdServer;
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
        if ($type && in_array($type, [1, 2])) {
            $query->andWhere([
                'type' => $type
            ]);
        } else {
            $query->andWhere([
                'in', 'type', [1, 2]
            ]);
        }
        $keyword = \Yii::$app->request->post('keyword');

        if ($keyword) {
            $query->andFilterWhere([
                'or',
                ['apply_id' => $keyword],
                ['title' => $keyword]
            ]);
        }

        $beginTime = \Yii::$app->request->post('begin_time');
        $endTime = \Yii::$app->request->post('end_time');
        if ($beginTime && $endTime) {
            $query->andWhere([
                'and',
                ['>=', 'create_time', strtotime($beginTime)],
                ['<', 'create_time', strtotime('+1day', strtotime($beginTime))],
            ]);
        }

        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount]);

        //当前页
        $currentPage = \Yii::$app->request->post('currentPage') ?: 1;
        //每页显示条数
        $perPage = \Yii::$app->request->post('perPage') ?: 20;

        $pagination->setPageSize($perPage, true);

        $pagination->setPage($currentPage - 1);
        $models = $query->limit($pagination->getLimit())->offset(
            $pagination->getPage() * $pagination->pageSize
        )->orderBy(['create_time' => SORT_DESC])->all();
        $data = [];
        if (!empty($models)) {
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

    /**
     * 获取确认表单
     *
     * @param $applyId
     * @param array $person
     * @return array|bool
     */
    public function getForm($applyId, $person)
    {
        $apply = Apply::findOne($applyId);
        if ($apply->status != 4 || !in_array($apply->type, [1, 2])) {
            $this->errorCode = 1010;
            $this->error = '申请ID不能确认，请求不合法';
            return false;
        }
        if ($apply->type == 1) {
            return [
                'pay_org' => PersonLogic::instance()->getOrg(),
                'pay_bank' => ThirdServer::instance()->getAccount($person['org_id']),
                'tags' => TreeTagLogic::instance()->getTreeTagsByParentId(2),
                'bank_card_id' => $apply->expense->bank_card_id,
                'bank_name' => $apply->expense->bank_name,
                'bank_name_des' => $apply->expense->bank_name_des,
            ];
        }
        if ($apply->type == 2) {
            return [
                'pay_org' => PersonLogic::instance()->getOrg(),
                'pay_bank' => ThirdServer::instance()->getAccount($person['org_id']),
                'tags' => TreeTagLogic::instance()->getTreeTagsByParentId(2),
                'bank_card_id' => $apply->loan->bank_card_id,
                'bank_name' => $apply->loan->bank_name,
                'bank_name_des' => $apply->loan->bank_name_des,
            ];
        }

    }

}