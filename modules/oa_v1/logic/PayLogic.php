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
use app\models\Person;
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
     * @param array $companyIds
     * @return array
     */
    public function canConfirmList($companyIds)
    {
        $type = \Yii::$app->request->post('type');

        $query = Apply::find()->alias('a')->leftJoin('oa_person b', 'a.person_id = b.person_id')->where([
            'a.cai_wu_need' => 2
        ]);
        
        $status = \Yii::$app->request->post('status');
        if($status && in_array($status, [4, 5, 6, 7, 99])) {
            if(in_array($status, [6, 7])) {
                $query->andWhere([
                    'in', 'a.status', [6, 7]
                ]);
            } else {
                $query->andWhere([
                    'a.status' => $status
                ]);
            }
        } else {
            $query->andWhere([
                'in', 'a.status', [4, 5, 6, 7, 99]
            ]);
        }
        //筛选
        if ($type) {
            if (is_array($type)) {
                $query->andWhere([
                    'in', 'a.type', $type
                ]);
            } else {
                $query->andWhere([
                    'a.type' => $type
                ]);
            }

        } else {
            $query->andWhere([
                'in', 'a.type', [1, 2, 4, 5]
            ]);
        }
        $keyword = trim(\Yii::$app->request->post('keyword'));

        if ($keyword) {
            $query->andFilterWhere([
                'or',
                ['like', 'a.apply_id', $keyword],
                ['like', 'a.title', $keyword]
            ]);
        }

        $beginTime = \Yii::$app->request->post('begin_time');
        $endTime = \Yii::$app->request->post('end_time');
        if ($beginTime && $endTime) {
            $query->andWhere([
                'and',
                ['>=', 'a.create_time', strtotime($beginTime)],
                ['<', 'a.create_time', strtotime('+1day', strtotime($endTime))],
            ]);
        }
        $query->andWhere([
            'in', 'a.company_id', $companyIds
        ]);
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount]);
        $order = 'a.create_time desc';
        if (\Yii::$app->request->post('sort')) {
            $order = 'a.create_time ' . \Yii::$app->request->post('sort');
        }

        //当前页
        $currentPage = \Yii::$app->request->post('page') ?: 1;
        //每页显示条数
        $perPage = \Yii::$app->request->post('page_size') ?: 20;

        $pagination->setPageSize($perPage, true);


        $pagination->setPage($currentPage - 1);
        $models = $query->limit($pagination->getLimit())->offset(
            $pagination->getPage() * $pagination->pageSize
        )->orderBy($order)->all();
        $data = [];
        if (!empty($models)) {
            /**
             * @var Apply $model
             */
            foreach ($models as $k => $model) {
                if ($model->type == 1) {
                    $typeName = '申请报销';
                    $money = $model->expense->money;
                } elseif ($model->type == 2) {
                    $typeName = '申请备用金';
                    $money = $model->loan->money;
                }elseif ($model->type == 4) {
                    $typeName = '申请付款';
                    $money = $model->applyPay->money;
                    $end_time = $model->applyPay->end_time;
                }else {
                    $typeName = '申请请购';
                    $money = $model->applyBuy->money;
                }

                $data[] = [
                    'id' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                    'create_time' => date('Y-m-d H:i', $model->create_time),
                    'type'  => $model->type,
                    'type_name' => $typeName,
                    'apply_id' => $model->apply_id,
                    'title' => $model->title,
                    'money' => $money,
                    'status' => $model->status,
                    'des' => $model->info->desInfo,
                    'end_time' => $end_time ? : '--',
                ];
            }
            return [
                'data' => $data,
                'page' => $this->pageFix($pagination)
            ];
        }
        return $data;
    }

    /**
     * 获取确认表单
     *
     * @param $applyId
     * @param Person $person
     * @param array $companyIds
     * @return array|bool
     */
    public function getForm($applyId, $person, $companyIds)
    {
        $apply = Apply::findOne($applyId);
        if ($apply->status != 4 || !in_array($apply->type, [1, 2, 4, 5])) {
            $this->errorCode = 1010;
            $this->error = '申请ID不能确认，请求不合法';
            return false;
        }
        switch ($apply->type) {
            case 1:
                $applyDetail = $apply->expense;
                break;
            case 2:
                $applyDetail = $apply->loan;
                break;
            case 4:
                $applyDetail = $apply->applyPay;
                break;
            default:
                $applyDetail = $apply->applyBuy;
                break;
        }
        if($apply->type == 4 || $apply->type == 5) {
            $data = [
                'pay_org' => PersonLogic::instance()->getSelectOrg($companyIds),
                'pay_bank' => ThirdServer::instance([
                    'token' => \Yii::$app->params['cai_wu']['token'],
                    'baseUrl' => \Yii::$app->params['cai_wu']['baseUrl']
                ])->getAccount($person['org_id']),
                'bank_card_id' => $applyDetail->bank_card_id,
                'bank_name' => $applyDetail->bank_name,
                'bank_name_des' => $applyDetail->bank_name_des,
                'name' => $applyDetail->to_name
            ];
        } else {
            $data = [
                'pay_org' => PersonLogic::instance()->getSelectOrg($companyIds),
                'pay_bank' => ThirdServer::instance([
                    'token' => \Yii::$app->params['cai_wu']['token'],
                    'baseUrl' => \Yii::$app->params['cai_wu']['baseUrl']
                ])->getAccount($person['org_id']),
                'bank_card_id' => $applyDetail->bank_card_id,
                'bank_name' => $applyDetail->bank_name,
                'bank_name_des' => $applyDetail->bank_name_des,
                'name' => $apply->person
            ];
        }
        if($apply->type == 1) {
            $data['tags'] = TreeTagLogic::instance()->getTreeTagsByParentId();
            foreach ($applyDetail->list as $v) {
                $data['list'][] = [
                    'id' => $v->id,
                    'money' => $v->money,
                    //'type_name' => $v->type_name,
                    //'type' => $v->type,
                    'des' => $v->des
                ];
            }
        } else {
            $data['tags'] = TreeTagLogic::instance()->getTreeTagsByParentId(2);
        }
        return $data;
    }

    /**
     * 导出付款确认列表
     *
     * @param array $companyIds
     */
    public function export($companyIds)
    {
        $type = \Yii::$app->request->get('type');

        $query = Apply::find()->alias('a')->leftJoin('oa_person', 'a.person_id = oa_person.person_id')->where([
            'a.status' => 4,
        ]);
        //筛选
        if ($type && in_array($type, [1, 2])) {
            if (is_array($type)) {
                $query->andWhere([
                    'in', 'a.type', $type
                ]);
            } else {
                $query->andWhere([
                    'a.type' => $type
                ]);
            }
        } else {
            $query->andWhere([
                'in', 'a.type', [1, 2]
            ]);
        }
        $keyword = trim(\Yii::$app->request->get('keyword'));

        if ($keyword) {
            $query->andFilterWhere([
                'or',
                ['a.apply_id' => $keyword],
                ['a.title' => $keyword]
            ]);
        }

        $beginTime = \Yii::$app->request->get('begin_time');
        $endTime = \Yii::$app->request->get('end_time');
        if ($beginTime && $endTime) {
            $query->andWhere([
                'and',
                ['>=', 'a.create_time', strtotime($beginTime)],
                ['<', 'a.create_time', strtotime('+1day', strtotime($endTime))],
            ]);
        }

        $order = 'a.create_time desc';
        if (\Yii::$app->request->get('desc')) {
            $order = \Yii::$app->request->get('desc') . ' desc';
        }

        if (\Yii::$app->request->get('asc')) {
            $order = \Yii::$app->request->get('asc') . ' asc';
        }
        $query->andWhere([
            'in', 'oa_apply.company_id', $companyIds
        ]);
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
                    $typeName = '申请备用金';
                    $money = $model->loan->money;
                    $bankName = $model->loan->bank_name;
                    $bankCardId = $model->loan->bank_card_id;
                }else if ($model->type == 4) {
                    $typeName = '申请付款';
                    $money = $model->applyPay->money;
                    $bankName = $model->applyPay->bank_name;
                    $bankCardId = $model->applyPay->bank_card_id;
                } else if ($model->type == 5) {
                    $typeName = '申请请购';
                    $money = $model->applyBuy->money;
                    $bankName = $model->applyBuy->bank_name;
                    $bankCardId = $model->applyBuy->bank_card_id;
                }  else {
                    $typeName = '申请还款';
                    $money = $model->payBack->money;
                    $bankName = $model->payBack->bank_name;
                    $bankCardId = $model->payBack->bank_card_id;
                }

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
            'fileName' => '付款确认_' . date('YmdHi') . '.xlsx'
        ]);
    }
}