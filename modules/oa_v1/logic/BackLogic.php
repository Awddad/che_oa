<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/9
 * Time: 11:00
 */

namespace app\modules\oa_v1\logic;


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
     * @param $user
     * @return array
     */
    public function getCanBack($user)
    {
        $jieKuan = JieKuan::find()->innerJoin('oa_apply', 'oa_apply.apply_id = oa_jie_kuan.apply_id')->where([
            'oa_apply.status' => 99,
            'oa_jie_kuan.status' => 1,
            'oa_apply.person_id' => $user['person_id']
        ])->all();
        $data = [];
        if (!empty($jieKuan)) {
            foreach ($jieKuan as $k => $v) {
                $data[] = [
                    'apply_id' => $v->apply_id,
                    'money' => $v->money,
                    'get_money_time' => $v->get_money_time,
                    'des' => $v->des
                ];
            }
        }
        return $data;
    }

    /**
     * 待确认收款列表
     */
    public function canConfirmList()
    {
        $query = Apply::find()->where([
            'status' => 4
        ]);
        $query->andWhere([
            'type' => 3
        ]);


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
                $typeName = '';
                $money = 0;
                if ($model->type == 3) {
                    $typeName = '退还备用金';
                    $money = $model->payBack->money;
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