<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/9
 * Time: 11:00
 */

namespace app\modules\oa_v1\logic;


use app\logic\server\ThirdServer;
use app\models\Apply;
use app\models\JieKuan;
use yii\data\Pagination;


/**
 * 还款逻辑
 * Class BackLogic
 * @package app\modules\oa_v1\logic
 */
class BackLogic extends BaseLogic
{
    /**
     * @param $applyId
     * @return array|bool
     */
    public function backForm($applyId, $person)
    {
        $apply = Apply::findOne($applyId);
        if ($apply->status != 4 || $apply->type != 3) {
            $this->errorCode = 1010;
            $this->error = '申请ID不能确认，请求不合法';
            return false;
        }
        return [
            'pay_org' => PersonLogic::instance()->getOrg(),
            'pay_bank' => ThirdServer::instance([
                'token' => \Yii::$app->params['cai_wu']['token'],
                'baseUrl' => \Yii::$app->params['cai_wu']['baseUrl']
            ])->getAccount($person['org_id']),
            'tags' => TreeTagLogic::instance()->getTreeTagsByParentId(1),
            'bank_card_id' => $apply->payBack->bank_card_id,
            'bank_name' => $apply->payBack->bank_name,
            'bank_name_des' => $apply->payBack->bank_name_des,
        ];
    }

    /**
     * @param $user
     * @return array
     */
    public function getCanBack($user)
    {
        $jieKuan = JieKuan::find()->innerJoin('oa_apply', 'oa_apply.apply_id = oa_jie_kuan.apply_id')->where([
            'oa_apply.status' => 99,
            'oa_jie_kuan.status' => 99,
            'oa_apply.person_id' => $user['person_id']
        ])->asArray()->all();
        $data = [];
        if (!empty($jieKuan)) {
            foreach ($jieKuan as $k => $v) {
                $data[] = [
                    'apply_id' => $v['apply_id'],
                    'money' => $v['money'],
                    'get_money_time' => date('Y-m-d H:i', $v['get_money_time']),
                    'des' => $v['des']
                ];
            }
        }
        return $data;
    }

    /**
     * 待确认收款列表
     *
     * @param array $orgIds
     * @return array
     *
     */
    public function canConfirmList($orgIds)
    {
        $query = Apply::find()->where([
            'status' => 4
        ]);
        $query->andWhere([
            'type' => 3
        ]);

        $keyword = \Yii::$app->request->post('keyword');

        if ($keyword) {
            $query->andFilterWhere([
                'or',
                ['like', 'apply_id', $keyword],
                ['like', 'title', $keyword]
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
        if (\Yii::$app->request->post('sort')) {
            $order = 'create_time ' . \Yii::$app->request->post('sort');
        }

        $query->andWhere([
            'in', 'org_id', $orgIds
        ]);

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
        )->orderBy($order)->all();
        $data = [];
        if (!empty($models)) {
            foreach ($models as $k => $model) {
                $typeName = '';
                $money = 0;
                if ($model->type == 3) {
                    $typeName = '退还备用金';
                    $money = $model->payBack->money;
                }

                $data[] = [
                    'id' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                    'create_time' => date('Y-m-d H:i', $model->create_time),
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
     * 导出收款确认列表
     *
     * @param array $user
     * @param array $orgIds
     */
    public function export($orgIds)
    {
        $query = Apply::find()->where([
            'status' => 4,
            'type' => 3
        ]);

        $keyword = \Yii::$app->request->get('keyword');

        if ($keyword) {
            $query->andFilterWhere([
                'or',
                ['apply_id' => $keyword],
                ['title' => $keyword]
            ]);
        }

        $beginTime = \Yii::$app->request->get('begin_time');
        $endTime = \Yii::$app->request->get('end_time');
        if ($beginTime && $endTime) {
            $query->andWhere([
                'and',
                ['>=', 'create_time', strtotime($beginTime)],
                ['<', 'create_time', strtotime('+1day', strtotime($beginTime))],
            ]);
        }

        $order = 'create_time desc';
        if (\Yii::$app->request->get('desc')) {
            $order = \Yii::$app->request->get('desc') . ' desc';
        }

        if (\Yii::$app->request->get('asc')) {
            $order = \Yii::$app->request->get('asc') . ' asc';
        }

        $query->andWhere([
            'in', 'org_id', $orgIds
        ]);

        $models = $query->orderBy($order)->all();
        $data = [];
        if (!empty($models)) {
            foreach ($models as $model) {
                $typeName = '申请还款';
                $money = $model->payBack->money;
                $bankName = $model->payBack->bank_name;
                $bankCardId = $model->payBack->bank_card_id;
                $data[] = [
                    'name' => $model->person,
                    'bank_name' => $bankName,
                    'bank_card_id' => " $bankCardId",
                    'money' => $money,
                    'type' => $typeName,
                    'apply_id' => " $model->apply_id",
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
            'fileName' => '收款确认_' . date('YmdHi') . '.xlsx'
        ]);
    }
}