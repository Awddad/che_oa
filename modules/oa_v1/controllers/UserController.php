<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/9/4
 * Time: 10:58
 */

namespace app\modules\oa_v1\controllers;
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
    /**
     * @return array
     */
    public function actionIndex()
    {
        $model = new EmployeeInfoForm();
        $employee = $this->arrPersonInfo->employee;
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
        $param['person_id'] = $this->arrPersonInfo->person_id;
        $param['employee_id'] = $this->arrPersonInfo->employee->id;
        $model = new EmployeeAccountParent();
        if ($model->load(['EmployeeAccountParent' => $param]) && $model->save()) {
            return $this->_return(null);
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
            return $this->_return([]);
        } else {
            return $this->_returnError(4400, BaseLogic::instance()->getFirstError($accountParent->errors));
        }
    }
}