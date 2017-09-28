<?php
namespace app\modules\oa_v1\controllers;

use app\models\EmployeeAccountParent;
use app\models\PeopleAbility;
use app\models\PeopleEduExperience;
use app\models\PeopleFiles;
use app\models\PeopleProjectExperience;
use app\models\PeopleTrainExperience;
use app\models\PeopleWorkExperience;
use app\modules\oa_v1\models\EmployeeForm;
use app\modules\oa_v1\models\PeopleForm;
use yii;
use app\modules\oa_v1\models\EmployeeInfoForm;
use app\modules\oa_v1\logic\AssetLogic;
use app\models\Employee;
use app\modules\oa_v1\logic\EmployeeLogic;

/**
 * 员工
 * @author yjr
 *
 */
class EmployeeController extends BaseController
{
    public function beforeAction($action)
    {
        if(Yii::$app->request->get('edit_myself')) {
            $employee = Yii::$app->request->get('id') ? Yii::$app->request->get('id')
                : Yii::$app->request->post('id');
            if ($this->arrPersonInfo->employee->id != $employee) {
                return $this->_returnError(400);
            }
        }
        return parent::beforeAction($action);
    }
    
    /**
     * 获得员工列表
     */
    public function actionGetList()
    {
        $get = yii::$app->request->get();
        $model = new EmployeeForm();
        $data = $model->getList($get);
        return $this->_return($data);
    }
    
    /**
     * 添加员工
     */
    public function actionAddEmployee()
    {
        $post = yii::$app->request->post();
        $model = new EmployeeForm();
        $model->setScenario($model::SCENARIO_ADD_EMPLOYEE);
        $model->load(['EmployeeForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->addEmployee();
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    /**
     * 入职操作
     */
    public function actionEntry()
    {
        $post = yii::$app->request->post();
        $model = new EmployeeForm();
        $model->setScenario($model::SCENARIO_ENTRY);
        $model->load(['EmployeeForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->entry();
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    /**
     * 取消入职
     */
    public function actionCancel()
    {
        $post = yii::$app->request->post();
        $model = new EmployeeForm();
        $model->setScenario($model::SCENARIO_CANCEL);
        $model->load(['EmployeeForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()),'参数错误');
        }
        $res = $model->cancel();
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    /**
     * 获得员工基本信息
     */
    public function actionGetInfo()
    {
        $id = yii::$app->request->get('id');
        if($id){
            $model = new EmployeeInfoForm();
            $res = $model->getEmpInfo($id);
            if($res['status']){
                return $this->_return($res['data']);
            }else{
                return $this->_returnError(400,$res['msg']);
            }
        }
        return $this->_returnError(403,'id不能为空');
    }
    
    /**
     * 编辑员工基本信息
     */
    public function actionEditInfo()
    {
        $post = yii::$app->request->post();
        $model = new EmployeeInfoForm();
        $model->setScenario($model::SCENARIO_EMP_EDIT);
        $model->load(['EmployeeInfoForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->saveEmployee($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    
    
    /**
     * 获得员工帐号信息
     */
    public function actionGetAccount()
    {
        $id = yii::$app->request->get('id');
        if($id){
            $model = new EmployeeInfoForm();
            $res = $model->getAccount($id);
            if($res['status']){
                return $this->_return($res['data']);
            }else{
                return $this->_returnError(400,$res['msg']);
            }
        }
        return $this->_returnError(403,'id不能为空');
    }
    
    /**
     * 编辑员工帐号信息
     */
    public function actionEditAccount()
    {
        $post = yii::$app->request->post();
        $model = new EmployeeInfoForm();
        $model->setScenario($model::SCENARIO_EMP_ACCOUNT_EDIT);
        $model->load(['EmployeeInfoForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->saveAccount($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    /**
     * 获得员工银行卡信息
     */
    public function actionGetBank()
    {
        $id = yii::$app->request->get('id');
        if($id){
            $model = new EmployeeInfoForm();
            $res = $model->getBankCards($id);
            if($res['status']){
                return $this->_return($res['data']);
            }else{
                return $this->_returnError(400,$res['msg']);
            }
        }
        return $this->_returnError(403,'id不能为空');
    }
    /**
     * 删除银行卡
     */
    public function actionDelBank()
    {
        $post = yii::$app->request->post();
        $model = new EmployeeInfoForm();
        $model->setScenario($model::SCENARIO_EMP_BANK_DEL);
        $model->load(['EmployeeInfoForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->delBankCard($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    /**
     * 编辑银行卡
     */
    public function actionEditBank()
    {
        $post = yii::$app->request->post();
        $model = new EmployeeInfoForm();
        $model->setScenario($model::SCENARIO_EMP_BANK_EDIT);
        $model->load(['EmployeeInfoForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->saveBankCard($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    
    /**
     * 待归还资产
     * @return array
     */
    public function actionCanBackAsset()
    {
        $id = yii::$app->request->get('id');
        if($id && $emp = Employee::findOne($id)){
            $data = AssetLogic::instance()->getCanBackAsset($emp->person_id);
            return $this->_return($data);
        }
        return $this->_returnError(403,'id不正确');
    } 
    
    /**
     * 修改劳动合同
     */
    public function actionEditService()
    {
        $post = yii::$app->request->post();
        $model = new EmployeeInfoForm();
        $model->setScenario($model::SCENARIO_EMP_SERVICE_EDIT);
        $model->load(['EmployeeInfoForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->editService($this->arrPersonInfo);
        if($res['status']){
            return $this->_return('成功');
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
    /**
     * 获得劳动合同
     */
    public function actionGetService()
    {
        $id = yii::$app->request->get('id');
        $model = new EmployeeInfoForm();
        $res = $model->getService($id);
        if($res['status']){
            return $this->_return($res['data']);
        }
        return $this->_returnError(403,$res['msg']);
    }
    
    public function actionGetQqUser()
    {
        $name = yii::$app->request->get('name');
        $data = EmployeeLogic::instance()->getQQUser($name);
        if($data['status']){
            return $this->_return($data['data']);
        }else{
            return $this->_returnError(400,$data['msg']);
        }
    }

    public function actionIndex()
    {
        $model = new EmployeeInfoForm();
        $employee = Employee::findOne(Yii::$app->request->get('id'));
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

        $canBackAsset = AssetLogic::instance()->getCanBackAsset($employee->person_id);

        return $this->_return(compact('baseInfo', 'service', 'bandCards', 'account', 'workExp',
            'projectExp', 'eduExp', 'abilityDetail', 'trainExp', 'files', 'accountParents','canBackAsset'));
    }
}