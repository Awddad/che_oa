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
        $order = 'create_time desc';
        if (\Yii::$app->request->post('desc')) {
            $order = \Yii::$app->request->post('desc') . ' desc';
        }

        if (\Yii::$app->request->post('asc')) {
            $order = \Yii::$app->request->post('asc') . ' asc';
        }

        //当前页
        $currentPage = \Yii::$app->request->post('currentPage') ?: 1;
        //每页显示条数
        $perPage = \Yii::$app->request->post('perPage') ?: 20;

        $pagination->setPageSize($perPage, true);


        $pagination->setPage($currentPage - 1);
        $models = $query->limit($pagination->getLimit())->offset(
            $pagination->getPage() * $pagination->pageSize
        )->orderBy($order)->all();
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
                'pay_bank' => ThirdServer::instance([
                    'token' => \Yii::$app->params['cai_wu']['token'],
                    'baseUrl' => \Yii::$app->params['cai_wu']['baseUrl']
                ])->getAccount($person['org_id']),
                'tags' => TreeTagLogic::instance()->getTreeTagsByParentId(2),
                'bank_card_id' => $apply->expense->bank_card_id,
                'bank_name' => $apply->expense->bank_name,
                'bank_name_des' => $apply->expense->bank_name_des,
            ];
        }
        if ($apply->type == 2) {
            return [
                'pay_org' => PersonLogic::instance()->getOrg(),
                'pay_bank' => ThirdServer::instance([
                    'token' => \Yii::$app->params['cai_wu']['token'],
                    'baseUrl' => \Yii::$app->params['cai_wu']['baseUrl']
                ])->getAccount($person['org_id']),
                'tags' => TreeTagLogic::instance()->getTreeTagsByParentId(2),
                'bank_card_id' => $apply->loan->bank_card_id,
                'bank_name' => $apply->loan->bank_name,
                'bank_name_des' => $apply->loan->bank_name_des,
            ];
        }

    }

    /**
     * 导出付款确认列表
     *
     * @param array $user
     */
    public function export($user)
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

        $order = 'create_time desc';
        if (\Yii::$app->request->post('desc')) {
            $order = \Yii::$app->request->post('desc') . ' desc';
        }

        if (\Yii::$app->request->post('asc')) {
            $order = \Yii::$app->request->post('asc') . ' asc';
        }

        $models = $query->orderBy($order)->all();
        $data = [];
        if (!empty($models)) {
            foreach ($models as $model) {
                if ($model->type == 1) {
                    $typeName = '申请报销';
                    $money = $model->expense->money;
                    $bankName = $model->expense->bank_name;
                    $bankCardId = $model->expense->bank_card_id;
                } else if ($model->type == 2) {
                    $typeName = '申请借款';
                    $money = $model->loan->money;
                    $bankName = $model->loan->bank_name;
                    $bankCardId = $model->loan->bank_card_id;
                } else {
                    $typeName = '申请还款';
                    $money = $model->payBack->money;
                    $bankName = $model->payBack->bank_name;
                    $bankCardId = $model->payBack->bank_card_id;
                }

                $data[] = [
                    'name' => $user['person_name'],
                    'bank_name' => $bankName,
                    'bank_card_id' => $bankCardId,
                    'money' => $money,
                    'type' => $typeName,
                    'apply_id' => $model->apply_id,
                    'title' => $model->title
                ];
            }
        }
        \moonland\phpexcel\Excel::export([
            'models' => $data,
            'columns' => [
                'name', 'bank_name', 'bank_card_id', 'money', 'type', 'apply_id', 'title'
            ],
            'headers' => [
                'name' => '姓名',
                'bank_name' => '银行',
                'bank_card_id' => '卡号',
                'money' => '金额',
                'type' => '类别',
                'apply_id' => '审批单号',
                'title' => '标题'
            ],
            'fileName' => 'pay_confirm_'.date('YmdHi').'.xlsx'
        ]);
    }
}