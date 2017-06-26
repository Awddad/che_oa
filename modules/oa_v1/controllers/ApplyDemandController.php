<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/14
 * Time: 15:29
 */

namespace app\modules\oa_v1\controllers;


use app\models\ApplyDemand;
use app\modules\oa_v1\logic\BaseLogic;
use app\modules\oa_v1\logic\PersonLogic;
use Yii;
use app\models\Apply;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use app\modules\oa_v1\models\ApplyDemandForm;


/**
 * 需求单
 *
 * Class ApplyDemandController
 * @package app\modules\oa_v1\controllers
 */
class ApplyDemandController extends BaseController
{
    /**
     * @return array
     */
    public function verbs()
    {
        return [
            'index' => ['post'],
            'view' => ['get']
        ];
    }
    
    /**
     * 申请请购
     */
    public function actionIndex()
    {
        $model = new ApplyDemandForm();
        
        $param = \Yii::$app->request->post();
        $data['ApplyDemandForm'] = $param;
        if ($model->load($data) && $model->validate() &&  $model->save($this->arrPersonInfo)) {
            return $this->_return($model->apply_id);
        } else {
            return $this->_return($model->errors, 400);
        }
    }
    
    /**
     * 需求单列表
     *
     * @return array
     */
    public function actionList()
    {
        $param = Yii::$app->request->get();
        $query = Apply::find()->where([
            'status' => 99,
            'type' => 6
        ]);
        
        $keyword = ArrayHelper::getValue($param, 'keyword');
        if($keyword) {
            $query->andWhere([
                'or',
                ['like','apply_id', $keyword],
                ['like','title', $keyword]
            ]);
        }
        $time = ArrayHelper::getValue($param, 'time');;
        if (!empty($time) && strlen($time > 20)) {
            $beforeTime = strtotime(substr($time, 0, 10));
            $afterTime = strtotime('+1day', strtotime(substr($time, -10)));
            $query->andWhere(['between', 'create_time', $beforeTime, $afterTime]);
        }
        
        $page = ArrayHelper::getValue($param, 'page', 1);
        $pageSize = ArrayHelper::getValue($param, 'pageSize', 20);
    
        $pagination = new Pagination([
            'defaultPageSize' => $pageSize,
            'totalCount' => $query->count(),
        ]);
        $model = $query->orderBy(["create_time" => SORT_DESC])
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        $data = [];
        /**
         * @var Apply $v
         */
        foreach ($model as $k => $v) {
            $org = PersonLogic::instance()->getOrgName($v->personInfo);
            
            $detail = implode(',', ArrayHelper::getColumn($v->applyDemand, 'name'));
            
            $data[] = [
                'id' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                'apply_id' => $v->apply_id,
                'create_time' => date('Y-m-d H:i', $v->create_time),
                'person' => $v->person,
                'org' => implode('-', $org),
                'detail' => $detail,
                'status' => ApplyDemand::STATUS[$v->applyDemand->status]
            ];
        }
        return $this->_return([
            'list' => $data,
            'pages' => BaseLogic::instance()->pageFix($pagination)
        ]);
    }
    
    /**
     * 需求单审核通过后，确认请购
     *
     * @return array
     */
    public function actionConfirmBuy()
    {
        $param = Yii::$app->request->post();
        $applyId = ArrayHelper::getValue($param, 'apply_id');
        $buyType = ArrayHelper::getValue($param, 'buy_type');
        $applyBuyId = ArrayHelper::getValue($param, 'apply_buy_id');
        $tips = ArrayHelper::getValue($param, 'tips', '');
        if(!$buyType || !$applyBuyId) {
            return $this->_returnError(400, [], '缺少必填参数');
        }
        $apply = ApplyDemand::findOne($applyId);
        $apply->buy_type = $buyType;
        $apply->apply_buy_id = $applyBuyId;
        $apply->tips = $tips;
        if (!$apply->save()) {
            return $this->_returnError(500, [], '确认失败');
        }
        return $this->_return([]);
    }
    
}