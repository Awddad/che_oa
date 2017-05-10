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
use app\models\PayBack;
use yii\data\Pagination;
use yii\web\UploadedFile;


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
    public function backForm($applyId)
    {
        $apply = Apply::findOne($applyId);
        if($apply->status != 4 || $apply->type != 3) {
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