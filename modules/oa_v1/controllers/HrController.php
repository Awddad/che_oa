<?php
namespace app\modules\oa_v1\controllers;

use app\models\Apply;
use app\models\ApplyTransfer;
use app\models\Employee;
use app\models\EmployeeAccount;
use app\models\Job;
use app\models\Talent;
use app\modules\oa_v1\logic\BackLogic;
use Yii;
use yii\data\Pagination;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class HrController extends BaseController
{
	public function actionEmployeeList()
	{
		
	}

	public function actionEntryList()
	{
		$get = Yii::$app->request->get();
        $keywords = ArrayHelper::getValue($get,'keywords',null);
        $start_time = ArrayHelper::getValue($get,'start_time',null);
        $end_time = ArrayHelper::getValue($get,'end_time',null);
        $page_size = ArrayHelper::getValue($get,'page_size',10);

        $query = (new Query())
            ->select('e.*,t.owner,a.tel')
            ->from(Employee::tableName().' e')
            ->leftJoin(Talent::tableName().' t', 'e.id=t.employee_id')
            ->leftJoin(EmployeeAccount::tableName().' a','a.employee_id=e.id')
            ->where(['>', 'e.person_id', 0]);

        if($keywords){
            $keywords = mb_convert_encoding($keywords,'UTF-8','auto');
            $query->andWhere("instr(CONCAT(e.name,e.person_id),'{$keywords}') > 0 ");
        }

        //开始时间
        if($start_time){
            $query->andWhere("date(e.entry_time) >= '{$start_time}'");
        }
        //结束时间
        if($end_time){
            $query->andWhere("date(e.entry_time) <= '{$end_time}'");
        }

        //echo $query->createCommand()->getRawSql();die();

        //分页
        $pagination = new Pagination([
            'defaultPageSize' => $page_size,
            'totalCount' => $query->count(),
        ]);

        $res = $query->orderBy("person_id desc")
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $data = [];
        foreach($res as $k=>$v){
            $data[] = [
                'id' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                'empno' => $v['person_id'],
                'name' => $v['name'],
                'tel' => $v['tel'] ?: $v['phone'],
                'job' => ($job = Job::findOne($v['profession'])) ? $job->name : '',
                'entry_time' => $v['entry_time'],
                'hr' => $v['owner'],
                'emp_id' => $v['id'],
            ];
        }

        return $this->_return([
            'res' => $data,
            'page' => BackLogic::instance()->pageFix($pagination)
        ]);
	}

    public function actionLeaveList()
    {
        $get = Yii::$app->request->get();
        $keywords = ArrayHelper::getValue($get,'keywords',null);
        $start_time = ArrayHelper::getValue($get,'start_time',null);
        $end_time = ArrayHelper::getValue($get,'end_time',null);
        $page_size = ArrayHelper::getValue($get,'page_size',10);

        $query = (new Query())
            ->select('e.*,t.owner,a.tel')
            ->from(Employee::tableName().' e')
            ->leftJoin(Talent::tableName().' t', 'e.id=t.employee_id')
            ->leftJoin(EmployeeAccount::tableName().' a','a.employee_id=e.id')
            ->where(['!=', 'leave_time', '']);

        if($keywords){
            $keywords = mb_convert_encoding($keywords,'UTF-8','auto');
            $query->andWhere("instr(CONCAT(e.name,e.person_id),'{$keywords}') > 0 ");
        }

        //开始时间
        if($start_time){
            $query->andWhere("date(e.leave_time) >= '{$start_time}'");
        }
        //结束时间
        if($end_time){
            $query->andWhere("date(e.leave_time) <= '{$end_time}'");
        }

        //echo $query->createCommand()->getRawSql();die();

        //分页
        $pagination = new Pagination([
            'defaultPageSize' => $page_size,
            'totalCount' => $query->count(),
        ]);

        $res = $query->orderBy("person_id desc")
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $data = [];
        foreach($res as $k=>$v){
            $data[] = [
                'id' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                'empno' => $v['person_id'],
                'name' => $v['name'],
                'tel' => $v['tel'] ?: $v['phone'],
                'job' => ($job = Job::findOne($v['profession'])) ? $job->name : '',
                'leave_time' => $v['leave_time'],
                'hr' => $v['owner'],
                'emp_id' => $v['id'],
            ];
        }

        return $this->_return([
            'res' => $data,
            'page' => BackLogic::instance()->pageFix($pagination)
        ]);
    }

    public function actionTransferList()
    {
        $get = Yii::$app->request->get();
        $keywords = ArrayHelper::getValue($get,'keywords',null);
        $start_time = ArrayHelper::getValue($get,'start_time',null);
        $end_time = ArrayHelper::getValue($get,'end_time',null);
        $page_size = ArrayHelper::getValue($get,'page_size',10);

        $query = (new Query())
            ->select('e.*,t.owner,a.tel,tr.transfer_time')
            ->from(Employee::tableName().' e')
            ->leftJoin(Talent::tableName().' t', 'e.id=t.employee_id')
            ->leftJoin(EmployeeAccount::tableName().' a','a.employee_id=e.id')
            ->rightJoin(Apply::tableName().' app','app.person_id=e.person_id')
            ->leftJoin(ApplyTransfer::tableName().' tr','tr.apply_id=app.apply_id')
            ->where(['>','e.person_id',0])
            ->andWhere(['app.type'=>12,'app.status'=>99]);

        if($keywords){
            $keywords = mb_convert_encoding($keywords,'UTF-8','auto');
            $query->andWhere("instr(CONCAT(e.name,e.person_id),'{$keywords}') > 0 ");
        }

        //开始时间
        if($start_time){
            $start_time = strtotime($start_time);
            $query->andWhere(['>=', 'app.create_time', $start_time]);
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

        $res = $query->orderBy("person_id desc")
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $data = [];
        foreach($res as $k=>$v){
            $data[] = [
                'id' => $pagination->pageSize * $pagination->getPage() + $k + 1,
                'empno' => $v['person_id'],
                'name' => $v['name'],
                'tel' => $v['phone'],
                'job' => ($job = Job::findOne($v['profession'])) ? $job->name : '',
                'entry_time' => $v['entry_time'],
                'change_time' => $v['transfer_time'],
                'hr' => $v['owner'],
                'emp_id' => $v['id'],
            ];
        }

        return $this->_return([
            'res' => $data,
            'page' => BackLogic::instance()->pageFix($pagination)
        ]);
    }

    public function actionCount()
    {
        $query = (new Query())
            ->from(Employee::tableName().' e')
            ->leftJoin(Talent::tableName().' t', 'e.id=t.employee_id')
            ->leftJoin(EmployeeAccount::tableName().' a','a.employee_id=e.id')
            ->where(['>', 'e.person_id', 0]);
        $entry_count = $query->count();

        $query = (new Query())
            ->from(Employee::tableName().' e')
            ->leftJoin(Talent::tableName().' t', 'e.id=t.employee_id')
            ->leftJoin(EmployeeAccount::tableName().' a','a.employee_id=e.id')
            ->where(['!=', 'leave_time', '']);
        $leave_count = $query->count();

        $query = (new Query())
            ->from(Employee::tableName().' e')
            ->leftJoin(Talent::tableName().' t', 'e.id=t.employee_id')
            ->leftJoin(EmployeeAccount::tableName().' a','a.employee_id=e.id')
            ->rightJoin(Apply::tableName().' app','app.person_id=e.person_id')
            ->leftJoin(ApplyTransfer::tableName().' tr','tr.apply_id=app.apply_id')
            ->where(['>','e.person_id',0])
            ->andWhere(['app.type'=>12,'app.status'=>99]);
        $tran_count = $query->count();

        $data = [
            'entry_count' => $entry_count,
            'leave_count' => $leave_count,
            'tran_count' => $tran_count,
        ];
        return $this->_return($data);
    }
}