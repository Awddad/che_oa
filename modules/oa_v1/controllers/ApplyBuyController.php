<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/13
 * Time: 13:52
 */

namespace app\modules\oa_v1\controllers;


use app\models\ApplyBuy;
use app\modules\oa_v1\logic\BaseLogic;
use app\modules\oa_v1\logic\PersonLogic;
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
            return $this->_return($model->errors, 400);
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
            $org = PersonLogic::instance()->getOrgName($v->apply->personInfo);
            
            $data[] = [
                'index' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                'apply_id' => $v->apply_id,
                'create_time' => date('Y-m-d H:i', $v->create_time),
                'person' => $v->person,
                'org' => $org,
                'money' => $v->applyBuy->money,
                'status' => ApplyBuy::STATUS[$v->status]
            ];
        }
        return $this->_return([
            'list' => $data,
            'pages' => BaseLogic::instance()->pageFix($pagination)
        ]);
    }
    
}