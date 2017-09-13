<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/9/4
 * Time: 10:58
 */

namespace app\modules\oa_v1\controllers;

use app\modules\oa_v1\logic\OrgLogic;
use moonland\phpexcel\Excel;
use Yii;
use app\models\Employee;
use app\models\EmployeeAccountParent;
use app\models\PeopleAbility;
use app\models\PeopleEduExperience;
use app\models\PeopleFiles;
use app\models\PeopleProjectExperience;
use app\models\PeopleTrainExperience;
use app\models\PeopleWorkExperience;
use app\modules\oa_v1\logic\BaseLogic;
use app\modules\oa_v1\models\EmployeeInfoForm;
use app\modules\oa_v1\models\PeopleForm;

/**
 * 用户相关
 *
 * Class UserController
 * @package app\modules\oa_v1\controllers
 */
class UserController extends BaseController
{
    public function beforeAction($action)
    {
        if(Yii::$app->request->get('edit_myself')) {
            $employee = Yii::$app->request->get('employee') ? Yii::$app->request->get('employee') : Yii::$app->request->post('employee');
            if ($this->arrPersonInfo->employee->id != $employee) {
                return $this->_returnError(400);
            }
        }
        return parent::beforeAction($action);
    }
    
    /**
     * @return array
     */
    public function actionIndex()
    {
        $model = new EmployeeInfoForm();
        $employee = $this->arrPersonInfo->employee;
        if (empty($employee)) {
            return [];
        }
        
        $baseInfo = $model->getEmpInfoByEmployee($employee);
        $service  = $model->getServiceEmployee($employee);
        $bandCards  = $model->getBandCardsEmployee($employee);
        $account  = $model->getPersonAccount($employee->id);
        $people = new PeopleForm();
        // 工作经验
        $workExperience = PeopleWorkExperience::find()->where(['employee_id' => $employee->id])->all();
        $workExp = [];
        if(!empty($workExperience)) {
            foreach ($workExperience as $v) {
                $workExp[] = $people->workExp($v);
            }
        }
        // 项目经验
        $projectExperience = PeopleProjectExperience::find()->where(['employee_id' => $employee->id])->all();
        $projectExp = [];
        if(!empty($projectExperience)) {
            foreach ($projectExperience as $v) {
                $projectExp[] = $people->projectExp($v);
            }
        }
        //教育经历
        $eduExperience = PeopleEduExperience::find()->where(['employee_id' => $employee->id])->all();
        $eduExp = [];
        if(!empty($eduExperience)) {
            foreach ($eduExperience as $v) {
                $eduExp[] = $people->eduExp($v);
            }
        }
        //技能评价
        $ability = PeopleAbility::find()->where(['employee_id' => $employee->id])->all();
        $abilityDetail = [];
        if(!empty($ability)) {
            foreach ($ability as $v) {
                $abilityDetail[] = $people->ability($v);
            }
        }
    
        //培训经历
        $trainExperience = PeopleTrainExperience::find()->where(['employee_id' => $employee->id])->all();
        $trainExp = [];
        if(!empty($trainExperience)) {
            foreach ($trainExperience as $v) {
                $trainExp[] = $people->trainExp($v);
            }
        }
    
        //上传附件
        $file = PeopleFiles::find()->where(['employee_id' => $employee->id])->all();
        $files = [];
        if(!empty($file)) {
            foreach ($file as $v) {
                $files[] = $people->files($v);
            }
        }
        
        $accountParent = EmployeeAccountParent::find()->where(['employee_id' => $employee->id])->all();
        $accountParents = [];
        if($accountParent) {
            /**
             * @var EmployeeAccountParent $v
             */
            foreach ($accountParent as $v) {
                $accountParents[] = [
                    'id' => $v->id,
                    'name' => $v->name,
                    'relation' => $v->relation,
                    'idnumber' => $v->idnumber,
                    'bank_name' => $v->bank_name,
                    'bank_card' => $v->bank_card,
                ];
            }
        }
        
        return $this->_return(compact('baseInfo', 'service', 'bandCards', 'account', 'workExp',
            'projectExp', 'eduExp', 'abilityDetail', 'trainExp', 'files', 'accountParents'));
    }
    
    /**
     * 新增孝工资卡
     *
     * @return array
     */
    public function actionAddAccountParent()
    {
        $param = \Yii::$app->request->post();
        
        $employee = \Yii::$app->request->post('employee_id');
        if (!$employee) {
            return $this->_returnError(403);
        }
        $param['person_id'] = Employee::findOne($employee)->person_id;
        
        $model = new EmployeeAccountParent();
        if ($model->load(['EmployeeAccountParent' => $param]) && $model->save()) {
            return $this->_return($model);
        } else {
            return $this->_returnError(4400, BaseLogic::instance()->getFirstError($model->errors));
        }
    }
    
    /**
     * 更新孝工资卡
     *
     * @param $account_parent_id
     *
     * @return array
     */
    public function actionEditAccountParent($account_parent_id)
    {
        $param = \Yii::$app->request->post();
        $accountParent = EmployeeAccountParent::findOne($account_parent_id);
        if ($accountParent->load(['EmployeeAccountParent' => $param]) && $accountParent->save()) {
            return $this->_return($accountParent);
        } else {
            return $this->_returnError(4400, BaseLogic::instance()->getFirstError($accountParent->errors));
        }
    }
    
    /**
     * 删除孝工资卡
     *
     * @return array
     */
    public function actionDeleteAccountParent()
    {
        $account_parent_id = Yii::$app->request->post('account_parent_id');
        $accountParent = EmployeeAccountParent::findOne($account_parent_id);
        if ($accountParent->delete()) {
            return $this->_return([]);
        } else {
            return $this->_returnError(4400, BaseLogic::instance()->getFirstError($accountParent->errors));
        }
    }
    
    /**
     * 删除孝工资卡
     *
     * @param $employee_id
     *
     * @return array
     */
    public function actionAccountParent($employee_id)
    {
        $accountParent = EmployeeAccountParent::find()->where(['employee_id' => $employee_id])->all();
        return $this->_return($accountParent);
    }
    
    /**
     *
     */
    public function actionAccountParentExport()
    {
        $employee = EmployeeAccountParent::find();
        Excel::export([
            'models' => $employee->all(),
            'columns' => [
                [
                    'header' => '员工姓名',
                    'value' => function($data) {
                        return $data->employee->name;
                    }
                ],
                [
                    'header' => '所属组织',
                    'value' => function($data) {
                        return OrgLogic::instance()->getOrgName($data->employee->org_id);
                    }
                ],
                'name',
                'relation',
                [
                    'attribute' => 'idnumber',
                    'value' => function($data) {
                        return "'".strval($data->idnumber);
                    },
                ],
                'bank_name',
                [
                    'attribute' => 'bank_card',
                    'value' => function($data) {
                        return "'".strval($data->bank_card);
                    },
                ],
            ],
            'fileName' => '员工孝工资卡'
        ]);
    }

    /**
     * 员工编号导出
     */
    public function actionEmpnoExport()
    {
        Excel::export([
            'models' => Employee::find()->where(['status'=>2])->all(),
            'columns' => [
                [
                    'header' => '员工编号',
                    'value' => function($data) {
                        return $data->person_id;
                    }
                ],
                [
                    'header' => '公司',
                    'value' => function($data) {
                        return OrgLogic::instance()->getCompany($data->org_id);
                    }
                ],
                [
                    'header' => '部门',
                    'value' => function($data) {
                        return $data->org ? $data->org->org_name : '';
                    }
                ],
                [
                    'attribute' => '职位',
                    'value' => function($data) {
                        return $data->job?$data->job->name:'';
                    },
                ],
                [
                    'attribute' => '姓名',
                    'value' => function($data) {
                        return $data->name;
                    },
                ],
            ],
            'fileName' => '员工编号'
        ]);
    }
}