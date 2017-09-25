<?php
/**
 * Created by PhpStorm.
 * User: YJR
 * Date: 2017/9/25
 * Time: 11:46
 */

namespace app\modules\oa_v1\controllers;

use app\models\Apply;
use app\models\ApplyRetire;
use app\modules\oa_v1\logic\BackLogic;
use Yii;
use app\modules\oa_v1\models\ApplyRetireForm;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class ApplyRetireController extends BaseController
{
    public function actionApply()
    {
        $post = Yii::$app->request->post();
        $data['ApplyRetireForm'] = $post;
        $model = new ApplyRetireForm();
        $model->setScenario($model::SCENARIO_APPLY);
        $model->load($data);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->saveApply($this->arrPersonInfo);
        if($res['status']){
            return $this->_return($res['apply_id']);
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }

    public function actionList()
    {
        $param = Yii::$app->request->get();
        $keywords = ArrayHelper::getValue($param,'keywords');
        $start_time = ArrayHelper::getValue($param,'start_time');
        $end_time = ArrayHelper::getValue($param,'end_time');
        $page_size = ArrayHelper::getValue($param,'page_size',10);
        $status = ArrayHelper::getValue($param,'status',null);//是否已处理

        $query = ApplyRetire::find()
            ->alias('r')
            ->rightJoin(Apply::tableName().' a','a.apply_id = r.apply_id')
            ->where(['a.type'=>19,'a.status'=>99]);

        //是否已处理
        if($status){
            $query->andWhere(['r.is_execute'=>$status]);
        }

        //关键词
        if($keywords){
            $keywords = mb_convert_encoding($keywords,'UTF-8','auto');
            $query->andWhere("instr(CONCAT(r.person_name,r.tel,r.profession),'{$keywords}') > 0 ");
        }

        //开始时间
        if($start_time){
            $start_time = strtotime($start_time);
            $query->andWhere(['>=', 'a.create_time', $start_time]);
        }
        //结束时间
        if($end_time){
            $end_time = strtotime($end_time.' 23:59:59');
            $query->andWhere(['<=', 'a.create_time', $end_time]);
        }

        //echo $query->createCommand()->getRawSql();die();

        //分页
        $pagination = new Pagination([
            'defaultPageSize' => $page_size,
            'totalCount' => $query->count(),
        ]);
        /**
         * @var $res ApplyRetire
         */
        $res = $query->orderBy("created_at desc")
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $data = [];
        if($res) {
            /**
             * @var $v ApplyRetire
             */
            foreach ($res as $k => $v) {
                $data[] = [
                    'id' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                    'apply_id' => $v->apply_id,
                    'person_name' => $v->person_name,
                    'tel' => $v->tel,
                    'job' => $v->profession,
                    'des' => $v->des,
                ];
            }
        }

        return $this->_return([
            'res' => $data,
            'page' => BackLogic::instance()->pageFix($pagination)
        ]);
    }
}