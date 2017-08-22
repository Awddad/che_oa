<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/14
 * Time: 15:29
 */

namespace app\modules\oa_v1\controllers;


use app\models\ApplyDemand;
use app\models\Role;
use app\modules\oa_v1\logic\BaseLogic;
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
            return $this->_returnError(4400, null, BaseLogic::instance()->getFirstError($model->errors));
        }
    }
    
    /**
     * 需求单列表
     *
     * @return array
     */
    public function actionList()
    {
        $role = Role::findOne(Yii::$app->session->get('ROLE_ID'));
        if($role->slug != 'xingzheng') {
            $this->_returnError(403);
        }
        $param = Yii::$app->request->get();
        $query = Apply::find()->alias('a')->innerJoin(
            'oa_apply_demand b', 'a.apply_id = b.apply_id'
        )->where([
            'a.status' => 99,
            'a.type' => 6
        ]);
        
        $keyword = trim(ArrayHelper::getValue($param, 'keywords'));
        if($keyword) {
            $query->andWhere([
                'or',
                ['like','a.apply_id', $keyword],
                ['like','a.title', $keyword]
            ]);
        }
        
        $filterStatus = $param['filter_status'];
        if ($filterStatus) {
            $filterStatusArray = explode(',', $filterStatus);
            $query->andWhere(['in', 'b.status', $filterStatusArray]);
        }
        
        $beforeTime = strtotime(ArrayHelper::getValue($param, 'start_time'));
        $afterTime = strtotime(ArrayHelper::getValue($param, 'end_time'));
        if ($beforeTime && $afterTime) {
            $afterTime = strtotime('+1day', $afterTime);
            $query->andWhere([
                'and',
                ['>', 'a.create_time', $beforeTime],
                ['<', 'a.create_time', $afterTime]
            ]);
        }
        
        $pageSize = ArrayHelper::getValue($param, 'page_size', 20);
    
        $pagination = new Pagination([
            'defaultPageSize' => $pageSize,
            'totalCount' => $query->count(),
        ]);
        $model = $query->orderBy(["a.create_time" => SORT_DESC])
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        $data = [];
        /**
         * @var Apply $v
         */
        foreach ($model as $k => $v) {
            $org = $v->personInfo->org_full_name;
            
            $detail = implode(',', ArrayHelper::getColumn($v->applyDemand->demandList, 'name'));
            
            $data[] = [
                'index' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                'apply_id' => $v->apply_id,
                'create_time' => date('Y-m-d H:i', $v->create_time),
                'person' => $v->person,
                'org' => $org,
                'detail' => $detail,
                'status' => ApplyDemand::STATUS[$v->applyDemand->status]
            ];
        }
        return $this->_return([
            'list' => $data,
            'filter_status' => ApplyDemand::STATUS,
            'page' => BaseLogic::instance()->pageFix($pagination)
        ]);
    }
    
    /**
     * 需求单审核通过后，确认请购
     *
     * @return array
     */
    public function actionConfirmBuy()
    {
        $model = new ApplyDemandForm();
        $model->scenario = $model::CONFIRM_BUY;
        
        $param = Yii::$app->request->post();
        $data['ApplyDemandForm'] = $param;
        if ($model->load($data) && $model->validate() && $model->confirmSave()) {
            return $this->_return($model->apply_id);
        } else {
            return $this->_returnError(4030, $model->errors, '确认失败');
        }
    }
    
}