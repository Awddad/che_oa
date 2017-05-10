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

    /**
     * 获取确认表单
     * @param $applyId
     */
    public function getForm($applyId)
    {
        $apply = Apply::findOne($applyId);
        if($apply->status != 4 || in_array($apply->type, [1, 2])) {
            $this->errorCode = 1010;
            $this->error = '申请ID不能确认，请求不合法';
            return false;
        }
        return [
            'pay_org' => $this->getPayOrg(),
            'pay_bank_card' => $this->getPayBankCard(),
            'car_type' => $this->getPayBankCard(),
            'bank_card_id' => $apply->payBack->bank_card_id,
            'bank_name' => $apply->payBack->bank_name,
            'bank_name_des' => $apply->payBack->bank_name_des,
        ];
    }

    public function getPayOrg()
    {
        return [
            1 => '部门1',
            2 => '部门2'
        ];
    }

    public function getBankCardId()
    {
        return [
            1 => '12345678912',
            2 => '32659798922'
        ];
    }

    public function getPayBankCard()
    {
        return [
            '1' => '类型1',
            '2' => '类型2'
        ];
    }

}