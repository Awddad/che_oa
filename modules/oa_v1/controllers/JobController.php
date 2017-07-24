<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/7/24
 * Time: 10:09
 */

namespace app\modules\oa_v1\controllers;


use app\modules\oa_v1\logic\BaseLogic;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use app\models\Job;
use Yii;

/**
 * 职位管理
 *
 * Class JobController
 * @package app\modules\oa_v1\controllers
 */
class JobController extends BaseController
{
    /**
     * 职位列表
     *
     * @return array
     */
    public function actionIndex()
    {
        $param = Yii::$app->request->get();
        $query = Job::find()->where(['is_delete' => 0]);
        $keyword = ArrayHelper::getValue($param, 'keywords');
        if ($keyword) {
            $query->andWhere([
                'or',
                ['like', 'name', $keyword],
                ['like', 'short_name', $keyword]
            ]);
        }
        $beforeTime = strtotime(ArrayHelper::getValue($param, 'start_time'));
        $afterTime = strtotime(ArrayHelper::getValue($param, 'end_time'));
        
        if ($beforeTime && $afterTime) {
            $afterTime = strtotime('+1day', $afterTime);
            $query->andWhere([
                'and',
                ['>', 'created_at', $beforeTime],
                ['<', 'created_at', $afterTime]
            ]);
        }
        $pageSize = ArrayHelper::getValue($param, 'page_size', 20);
        
        $pagination = new Pagination([
            'defaultPageSize' => $pageSize,
            'totalCount' => $query->count(),
        ]);
        $model = $query->orderBy(["created_at" => SORT_DESC])
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        $data = [];
        /**
         * @var Job $v
         */
        foreach ($model as $k => $v) {
            $parent = Job::findOne($v->pid);
            $data[] = [
                'index' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                'id' => $v->id,
                'name' => $v->name,
                'short_name' => $v->short_name,
                'pid' => $v->pid,
                'pid_name' => $parent ? $parent->name : '',
                'des' => $v->des,
            ];
        }
        
        return $this->_return([
            'list' => $data,
            'page' => BaseLogic::instance()->pageFix($pagination)
        ]);
    }
    
    
    /**
     * 删除职位
     *
     * @return array
     */
    public function actionDelete()
    {
        $id = Yii::$app->request->post('id');
        if (!$id) {
            return $this->_returnError(403);
        }
        $model = Job::findOne($id);
        $model->deleted_at = time();
        $model->is_delete = 1;
        if ($model->save()) {
            return $this->_return(true);
        }
        
        return $this->_returnError(400);
    }
    
    /**
     * 新增职位
     *
     * @return array
     */
    public function actionCreate()
    {
        $param = Yii::$app->request->post();
        $post['Job'] = $param;
        $job = new Job();
        if ($job->load($post) && $job->save()) {
            return $this->_return($job);
        } else {
            return $this->_returnError(4400, null, BaseLogic::instance()->getFirstError($job->errors));
        }
    }
    
    /**
     * 更新职位
     *
     * @return array
     */
    public function actionUpdate()
    {
        $id = Yii::$app->request->post('id');
        if (!$id) {
            return $this->_returnError(403);
        }
        $param = Yii::$app->request->post();
        unset($param['id']);
        $job = Job::findOne($id);
        $post['Job'] = $param;
        if ($job->load($post) && $job->save()) {
            return $this->_return($job);
        } else {
            return $this->_returnError(4400, null, BaseLogic::instance()->getFirstError($job->errors));
        }
    }
}