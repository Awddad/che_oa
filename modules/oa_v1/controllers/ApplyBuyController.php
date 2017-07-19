<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/13
 * Time: 13:52
 */

namespace app\modules\oa_v1\controllers;


use app\models\ApplyBuy;
use app\models\ApplyBuyList;
use app\modules\oa_v1\logic\AssetLogic;
use app\modules\oa_v1\logic\BaseLogic;
use Yii;
use app\models\Apply;
use app\modules\oa_v1\models\ApplyBuyForm;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

/**
 * 申请请购
 *
 * Class ApplyBuyController
 * @package app\modules\oa_v1\controllers
 */
class ApplyBuyController extends BaseController
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
        $model = new ApplyBuyForm();
        
        $param = \Yii::$app->request->post();
        $data['ApplyBuyForm'] = $param;
        if ($model->load($data) && $model->validate() &&  $model->save($this->arrPersonInfo)) {
            return $this->_return($model->apply_id);
        } else {
            return $this->_returnError(4400, null, BaseLogic::instance()->getFirstError($model->errors));
        }
    }
    
    /**
     * 行政- 请购列表
     *
     */
    public function actionList()
    {
        $param = Yii::$app->request->get();
        $query = Apply::find()->where([
            'status' => 99,
            'type' => 5
        ]);
    
        $keyword = ArrayHelper::getValue($param, 'keywords');
        if($keyword) {
            $query->andWhere([
                'or',
                ['like','apply_id', $keyword],
                ['like','title', $keyword]
            ]);
        }
        $beforeTime = strtotime(ArrayHelper::getValue($param, 'start_time'));
        $afterTime = strtotime(ArrayHelper::getValue($param, 'end_time'));
        
        if ($beforeTime && $afterTime) {
            $afterTime = strtotime('+1day', $afterTime);
            $query->andWhere([
                'and',
                ['>', 'create_time', $beforeTime],
                ['<', 'create_time', $afterTime]
            ]);
        }
    
        $pageSize = ArrayHelper::getValue($param, 'page_size', 20);
    
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
            $org = $v->personInfo->org_full_name;
            $data[] = [
                'index' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                'apply_id' => $v->apply_id,
                'create_time' => date('Y-m-d H:i', $v->create_time),
                'person' => $v->person,
                'org' => $org,
                'money' => $v->applyBuy->money,
                'status' => ApplyBuy::STATUS[$v->applyBuy->status]
            ];
        }
        return $this->_return([
            'list' => $data,
            'page' => BaseLogic::instance()->pageFix($pagination)
        ]);
    }
    
    /**
     * 入库列表
     * @param $apply_id
     *
     * @return array
     */
    public function actionBuyList($apply_id)
    {
        $data = ApplyBuyList::find()->where([
            'apply_id' => $apply_id
        ])->all();
        $returnData = [];
        if (!empty($data)) {
            foreach ($data as $value) {
                if ($value->amount <= $value->in_amount) {
                    continue;
                }
                $returnData[] = $value;
            }
        }
        return $this->_return($returnData);
    }
    
    /**
     * 入库
     */
    public function actionAddStock()
    {
        $param = Yii::$app->request->post();
        if(empty($param) || !isset($param['list']) || !isset($param['apply_id']) || empty($param['list'])) {
            return $this->_returnError(400, [], '参数错误');
        }
        if(!$param['apply_id']) {
            return $this->_returnError(4031, [], 'apply_id不能为空');
        }
        $data = AssetLogic::instance()->addAsset($param, $this->arrPersonInfo);
        return $this->_return($data);
    }
    
}