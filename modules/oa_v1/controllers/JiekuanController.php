<?php

namespace app\modules\oa_v1\controllers;

use app\modules\oa_v1\logic\BackLogic;
use app\modules\oa_v1\logic\JieKuanLogic;
use app\modules\oa_v1\logic\PersonLogic;
use Yii;
use app\models\JieKuan;
use yii\data\Pagination;

/**
 * 借款相关接口信息
 * @url
 */
class JiekuanController extends BaseController
{
    /**
     * 员工借款明细表
     * @param 关键字
     * @param 部门
     * @param 借款时间
     * @param 分页数
     * @param 每页条数
     * @param 默认借款时间排序
     * @return array
     */
    public function actionIndex()
    {
        // 获取相关参数
        $key = Yii::$app->request->get('key');
        $orgId = Yii::$app->request->get('orgId');
        $time = Yii::$app->request->get('time');
        $pageSize = Yii::$app->request->get('pageSize', 20);
        $sort = Yii::$app->request->get('sort', 'desc');
        $sort = (($sort == 'asc') ? 'ASC' : 'DESC');

        // 查询结构
        $query = JieKuan::find()->where(['is_pay_back' => 0])->andWhere([
            '>', 'get_money_time' , 0
        ]);

        // 关键字查询
        if (!empty($key)) {
            // 通过key获取用户id,再获取申请id
            $personIds = PersonLogic::getPersonIdsByKey($key);
            if ($personIds) {
                $applyIds = JieKuanLogic::getApplyIdsByPersonId($personIds);
            }

            // 借款事由模糊查询
            if (isset($applyIds) && $applyIds) {
                $query->andWhere(['or', ['like', 'des', $key], ['apply_id' => $applyIds]]);
            } else {
                $query->andWhere(['like', 'des', $key]);
            }
        }

        // 部门查询
        if (!empty($orgId)) {
            $personIds = PersonLogic::getPersonIdsByOrgId($orgId);
            if ($personIds) {
                $applyIds = JieKuanLogic::getApplyIdsByPersonId($personIds);
                $query->andFilterWhere(['apply_id' => $applyIds]);
            }
        }

        // 借款时间
        if (!empty($time) && strlen($time > 20)) {
            $beforeTime = strtotime(substr($time, 0, 10));
            $afterTime = strtotime('+1day', strtotime(substr($time, -10)));
            $query->andWhere(['between', 'get_money_time', $beforeTime, $afterTime]);
        }

        $pagination = new Pagination([
            'defaultPageSize' => $pageSize,
            'totalCount' => $query->count(),
        ]);
        $model = $query->orderBy("get_money_time {$sort}")
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        $data = [];
        foreach ($model as $k => $v) {
            if(!$v->apply)
                continue;
            $org = $v->apply->personInfo->org_full_name;
            
            $data[] = [
                'id' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                'apply_id' => $v->apply_id,
                'get_money_time' => Yii::$app->formatter->asDatetime($v->get_money_time),
                'money' => Yii::$app->formatter->asCurrency($v->money),
                'des' => $v->des,
                'person' => $v->apply->person,
                'org' => $org,
            ];
        }

        return $this->_return([
            'info' => $data,
            'page' => BackLogic::instance()->pageFix($pagination)
        ]);
    }


    public function actionExport()
    {
        // 获取相关参数
        $key = Yii::$app->request->get('key');
        $orgId = Yii::$app->request->get('orgId');
        $time = Yii::$app->request->get('time');

        // 查询结构
        $query = JieKuan::find()->where(['is_pay_back' => 0])->andWhere([
            '>', 'get_money_time' , 0
        ]);

        // 关键字查询
        if (!empty($key)) {
            // 通过key获取用户id,再获取申请id
            $personIds = PersonLogic::getPersonIdsByKey($key);
            if ($personIds) {
                $applyIds = JieKuanLogic::getApplyIdsByPersonId($personIds);
            }

            // 借款事由模糊查询
            if (isset($applyIds) && $applyIds) {
                $query->andWhere(['or', ['like', 'des', $key], ['apply_id' => $applyIds]]);
            } else {
                $query->andWhere(['like', 'des', $key]);
            }
        }

        // 部门查询
        if (!empty($orgId)) {
            $personIds = PersonLogic::getPersonIdsByOrgId($orgId);
            if ($personIds) {
                $applyIds = JieKuanLogic::getApplyIdsByPersonId($personIds);
                $query->andFilterWhere(['apply_id' => $applyIds]);
            }
        }

        // 借款时间
        if (!empty($time) && strlen($time > 20)) {
            $beforeTime = strtotime(substr($time, 0, 10));
            $afterTime = strtotime('+1day', strtotime(substr($time, -10)));
            $query->andWhere(['between', 'get_money_time', $beforeTime, $afterTime]);
        }


        $model = $query->all();
        $data = [];
        foreach ($model as $v) {
            if(!$v->apply)
                continue;
            $org = $v->apply->personInfo->org_full_name;
            $data[] = [
                'get_money_time' => date('Y-m-d H:i', $v->get_money_time),
                'person' => $v->apply->person,
                'org' => $org,
                'money' => Yii::$app->formatter->asCurrency($v->money),
                'des' => $v->des,
            ];
        }
        \moonland\phpexcel\Excel::export([
            'models' => $data,
            'columns' => [
                'get_money_time', 'person', 'org', 'money', 'des'
            ],
            'headers' => [
                'get_money_time' => '时间',
                'person' => '借款人',
                'org' => '部门',
                'money' => '金额',
                'des' => '是由',
            ],
            'fileName' => '备用金明细_'.date('YmdHi').'.xlsx'
        ]);
    }
}