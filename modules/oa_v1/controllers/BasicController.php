<?php
namespace app\modules\oa_v1\controllers;


use Yii;
use app\modules\oa_v1\models\BasicAssetForm;
use app\models\AssetBrand;
use app\modules\oa_v1\models\BasicPersonForm;
use app\models\PersonType;
use app\models\EmployeeType;
use app\modules\oa_v1\models\BasicEmployeeForm;
use app\models\AssetType;


class BasicController extends BaseController
{
    public function actionGetTypeAll()
    {
        $model = new BasicAssetForm();
        $res = $model->getAssetType();
        return $this->_return($res);
    }
    
	public function actionGetTypeList()
	{
		$params = yii::$app->request->get();
		$model = new BasicAssetForm();
		$res = $model->getAssetTypeList($params);
		$data = [
			'page' => $res ['pages'],
			'res' => []
		];
		foreach($res['data'] as $k => $v){
			$data['res'][] = [
					'number' => ($data['page']['currentPage']-1)*$data['page']['perPage'] + $k+1,
			        'id' => $v['id'],
					'name' => $v['name'],
			        'has_child' => $v['has_child'] ? 1 : 0, 
					'update_time' => date('Y-m-d H:i:s', $v['update_time']),
			];
		}
		return $this->_return($data);
	}
	
	public function actionGetTypeInfo()
	{
		$id = yii::$app->request->get('id');
		$model = AssetType::findOne($id);
		if($model){
			//$child = AssetType::find()->select('id,name')->where(['parent_id'=>$model->id])->asArray()->all();
			$data = ['id'=>$model->id,'name'=>$model->name];
			return $this->_return($data);
		}else{
			return $this->_returnError(403,'原数据不存在');
		}
	}
	
	/**
	 * 添加资产类型
     *
     * @return array
	 */
	public function actionAddType()
	{
		$post = yii::$app->request->post();
		$model = new BasicAssetForm();
		$model->setScenario($model::SCENARIO_ADD_TYPE);
		$model->load(['BasicAssetForm'=>$post]);
		
		if(!$model->validate()){
			return $this->_returnError(403,current($model->getFirstErrors()));
		}
		$res = $model->addType();
		if($res['status']){
			return $this->_return('成功');
		}else{
			return $this->_returnError(400,$res['msg']);	
		}
	}
    
    /**
     * 更新资产类型
     *
     * @return array
     */
	public function actionUpdateType()
	{
		$post = yii::$app->request->post();
		$model = new BasicAssetForm();
		$model->setScenario($model::SCENARIO_EDIT_TYPE);
		$model->load(['BasicAssetForm'=>$post]);
		
		if(!$model->validate()){
			return $this->_returnError(403,current($model->getFirstErrors()));
		}
		$res = $model->updateType();
		if($res['status']){
			return $this->_return('成功');
		}else{
			return $this->_returnError(400,$res['msg']);
		}
	}
    
    /**
     * 增加子资产类型
     *
     * @return array
     */
	public function actionAddChild()
	{
	    $post = yii::$app->request->post();
        $model = new BasicAssetForm();
		$model->setScenario($model::SCENARIO_ADD_CHILD);
		$model->load(['BasicAssetForm'=>$post]);
		
		if(!$model->validate()){
		    return $this->_returnError(403,current($model->getFirstErrors()));
		}
		$res = $model->addChild();
		if($res['status']){
		    return $this->_return('成功');
		}else{
		    return $this->_returnError(400,$res['msg']);
		}
	}
	/**
	 * 添加资产品牌
	 */
	public function actionAddBrand()
	{
		$post = yii::$app->request->post();
		$model = new BasicAssetForm();
		$model->setScenario($model::SCENARIO_ADD_BRAND);
		$model->load(['BasicAssetForm'=>$post]);
		
		if(!$model->validate()){
			return $this->_returnError(403,current($model->getFirstErrors()));
		}
		$res = $model->addBrand();
		if($res['status']){
			return $this->_return('成功');
		}else{
			return $this->_returnError(400,$res['msg']);
		}
	}
	/**
	 * 修改资产品牌
	 */
	public function actionUpdateBrand()
	{
		$post = yii::$app->request->post();
		$model = new BasicAssetForm();
		$model->setScenario($model::SCENARIO_EDIT_BRAND);
		$model->load(['BasicAssetForm'=>$post]);
		
		if(!$model->validate()){
			return $this->_returnError(403,current($model->getFirstErrors()));
		}
		$res = $model->updateBrand();
		if($res['status']){
			return $this->_return('成功');
		}else{
			return $this->_returnError(400,$res['msg']);
		}
	}
	/**
	 * 获得品牌列表
	 */
	public function actionGetBrandList()
	{
		$params = yii::$app->request->get();
		$model = new BasicAssetForm();
		$res = $model->getAssetBrandList($params);
		$data = [
				'page' => $res ['pages'],
				'res' => []
		];
		foreach($res['data'] as $k => $v){
			$data['res'][] = [
			        'number' => ($data['page']['currentPage']-1)*$data['page']['perPage'] + $k+1,
					'id' => $v['id'],
					'name' => $v['name'],
					'update_time' => date('Y-m-d H:i:s', $v['update_time']),
			];
		}
		return $this->_return($data);
	}
	/**
	 * 获得品牌详情
	 */
	public function actionGetBrandInfo()
	{
		$id = yii::$app->request->get('brand_id');
		$brand = AssetBrand::findOne($id);
		if($brand){
			$data = ['brand_id'=>$brand->id,'brand_name'=>$brand->name];
			return $this->_return($data);
		}else{
			return $this->_returnError(403,'原数据不存在');
		}
	}
	/**
	 * 获得人才类型列表
	 */
	public function actionGetPersonList()
	{
		$params = yii::$app->request->get();
		$model = new BasicPersonForm();
		$res = $model->getTypeList($params);
		$data = [
				'page' => $res ['pages'],
				'res' => []
		];
		foreach($res['data'] as $k => $v){
			$data['res'][] = [
					'number' => ($data['page']['currentPage']-1)*$data['page']['perPage'] + $k+1,
			        'id' => $v['id'],
					'name' => $v['name'],
					'update_time' => date('Y-m-d H:i:s', $v['update_time']),
			];
		}
		return $this->_return($data);
	}
	/**
	 * 添加人才类别
	 */
	public function actionAddPerson()
	{
		$post = yii::$app->request->post();
		$model = new BasicPersonForm();
		$model -> setScenario($model::SCENARIO_ADD);
		$model->load(['BasicPersonForm'=>$post]);
		if(!$model->validate()){
			return $this->_returnError(403,current($model->getFirstErrors()));
		}
		$res = $model->editType();
		if($res['status']){
			return $this->_return('成功');
		}else{
			return $this->_returnError(400,$res['msg']);
		}
	}
	/**
	 * 修改人才类别
	 */
	public function actionUpdatePerson()
	{
		$post = yii::$app->request->post();
		$model = new BasicPersonForm();
		$model -> setScenario($model::SCENARIO_EDIT);
		$model->load(['BasicPersonForm'=>$post]);
		if(!$model->validate()){
			return $this->_returnError(403,current($model->getFirstErrors()));
		}
		$res = $model->editType();
		if($res['status']){
			return $this->_return('成功');
		}else{
			return $this->_returnError(400,$res['msg']);
		}
	}
	/**
	 * 获得人才类别详情
	 */
	public function actionGetPersonInfo()
	{
		$id = yii::$app->request->get('id');
		$model = PersonType::findOne($id);
		if($model){
			$data = ['id'=>$model->id,'name'=>$model->name];
			return $this->_return($data);
		}else{
			return $this->_returnError(403,'原数据不存在');
		}
	}
	/**
	 * 获得员工类别列表
	 */
	public function actionGetEmployeeList()
	{
		$params = yii::$app->request->get();
		$model = new BasicEmployeeForm();
		$res = $model->getTypeList($params);
		$data = [
				'page' => $res ['pages'],
				'res' => []
		];
		foreach($res['data'] as $k => $v){
			$data['res'][] = [
					'number' => ($data['page']['currentPage']-1)*$data['page']['perPage'] + $k+1,
			        'id' => $v['id'],
					'name' => $v['name'],
					'update_time' => date('Y-m-d H:i:s', $v['update_time']),
					'default' => $v['default'],
			];
		}
		return $this->_return($data);
		
	}
	/**
	 * 获得员工类型详情
	 */
	public function actionGetEmployeeInfo()
	{
		$id = yii::$app->request->get('id');
		$model = EmployeeType::findOne($id);
		if($model){
			$data = ['id'=>$model->id,'name'=>$model->name];
			return $this->_return($data);
		}else{
			return $this->_returnError(403,'原数据不存在');
		}
	}
	/**
	 * 添加员工类型
	 */
	public function actionAddEmployee()
	{
		$post = yii::$app->request->post();
		$model = new BasicEmployeeForm();
		$model -> setScenario($model::SCENARIO_ADD);
		$model->load(['BasicEmployeeForm'=>$post]);
		if(!$model->validate()){
			return $this->_returnError(403,current($model->getFirstErrors()));
		}
		$res = $model->editType();
		if($res['status']){
			return $this->_return('成功');
		}else{
			return $this->_returnError(400,$res['msg']);
		}
	}
	/**
	 * 修改员工类型
	 */
	public function actionUpdateEmployee()
	{
		$post = yii::$app->request->post();
		$model = new BasicEmployeeForm();
		$model -> setScenario($model::SCENARIO_EDIT);
		$model->load(['BasicEmployeeForm'=>$post]);
		if(!$model->validate()){
			return $this->_returnError(403,current($model->getFirstErrors()));
		}
		$res = $model->editType();
		if($res['status']){
			return $this->_return('成功');
		}else{
			return $this->_returnError(400,$res['msg']);
		}
	}
}