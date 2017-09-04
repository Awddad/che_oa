<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/7/24
 * Time: 10:09
 */

namespace app\modules\oa_v1\controllers;


use app\logic\server\JobServer;
use app\models\Employee;
use app\models\Person;
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
        $keyword = trim(ArrayHelper::getValue($param, 'keywords'));
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
                'need_exam' => $v->need_exam,
                'need_exam_name' => $v->need_exam ? '是' : '否',
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
        $count = Employee::find()->where(['profession' => $model->name])->count();
        if ($count > 0) {
            return $this->_returnError(4400, null, '该职位有员工，不能删除！');
        }
        $rst = JobServer::instance([
            'token' => \Yii::$app->params['quan_xian']['auth_token'],
            'baseUrl' => \Yii::$app->params['quan_xian']['auth_api_url']
        ])->delete($id);
        if(true === $rst) {
            $model->deleted_at = time();
            $model->is_delete = 1;
            if ($model->save()) {
                return $this->_return(true);
            }
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
        
        if(!isset($param['name'])) {
            return $this->_returnError(403);
        }
        $rst = JobServer::instance([
            'token' => \Yii::$app->params['quan_xian']['auth_token'],
            'baseUrl' => \Yii::$app->params['quan_xian']['auth_api_url']
        ])->create( [
            'name' => $param['name'],
            'slug' => $param['short_name'] ? : '',
        ]);
        if($rst) {
            $param['id'] = $rst['id'];
            $post['Job'] = $param;
            $job = new Job();
            if ($job->load($post) && $job->save()) {
                return $this->_return($job);
            } else {
                return $this->_returnError(4400, null, BaseLogic::instance()->getFirstError($job->errors));
            }
        }
        return $this->_returnError(4400, null, JobServer::instance()->error);
    }
    
    /**
     * 更新职位
     *
     * @return array
     */
    public function actionUpdate()
    {
        $id = Yii::$app->request->post('id');
        $param = Yii::$app->request->post();
        if (!$id) {
            return $this->_returnError(403);
        }
        $job = Job::findOne($id);
        $rst = JobServer::instance([
            'token' => \Yii::$app->params['quan_xian']['auth_token'],
            'baseUrl' => \Yii::$app->params['quan_xian']['auth_api_url']
        ])->update($id, [
            'name' => isset($param['name']) ? $param['name'] : $job->name,
            'slug' => $param['short_name'] ? : '',
        ]);
        if(true === $rst) {
            $param = Yii::$app->request->post();
            unset($param['id']);
            $post['Job'] = $param;
            if ($job->load($post) && $job->save()) {
                return $this->_return($job);
            } else {
                return $this->_returnError(4400, null, BaseLogic::instance()->getFirstError($job->errors));
            }
        }
        return $this->_returnError(4400, null, '更新失败');
    }
    
    /**
     *  树形结构
     */
    public function actionAllJob()
    {
        $data =  [
            'value' => 0,
            'label' => '无',
            'children' => $this->getJob()
        ];
        return $this->_return($data);
        
    }
    
    public function getJob($pid = 0)
    {
        $job = Job::find()->select(['id', 'name'])->where([
            'pid' => $pid,
            'is_delete' => 0,
        ])->asArray()->all();
        if(empty($job)) {
            return false;
        }
        $data = [];
        foreach ($job as $v) {
            $child = $this->getJob($v['id']);
            if ($child) {
                $data[] = [
                    'value' => $v['id'],
                    'label' => $v['name'],
                    'children' => $child
                ];
            } else {
                $data[] = [
                    'value' => $v['id'],
                    'label' => $v['name'],
                ];
            }
        }
        return $data;
    }

    /**
     * 根据职位获得人
     * @return array
     */
    public function actionGetPerson()
    {
        $org_id = Yii::$app->request->get('org_id',0);
        if($org_id) {
            /**
             * @var $person Person
             */
            $person = Person::find()->where(['org_id' => $org_id])->all();
            $data =[];
            if($person) {
                foreach($person as $v) {
                    $data[] = [
                        'id' => $v->person_id,
                        'label' => $v->person_name,
                        'org' => $v->org_full_name,
                    ];
                }
            }
            return $this->_return($data);
        }else{
            return $this->_returnError(403,'org_id不能为空！');
        }
    }
}