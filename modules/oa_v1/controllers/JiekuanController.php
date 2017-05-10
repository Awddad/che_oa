<?php

namespace app\modules\oa_v1\controllers;

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

        // 查询结构
        $query = JieKuan::find()->where(['is_pay_back' => false]);

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
        if (!empty($time)) {
            $beforeTime = strtotime(substr($time, 0, 10));
            $afterTime = strtotime(substr($time, -1, 10));
            $query->andWhere(['between', 'get_money_time', $beforeTime, $afterTime]);
        }

        $pagination = new Pagination([
            'defaultPageSize' => $pageSize,
            'totalCount' => $query->count(),
        ]);

        $data = $query->orderBy('get_money_time DESC')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->_return(['info' => ['data' => $data]]);
    }
}