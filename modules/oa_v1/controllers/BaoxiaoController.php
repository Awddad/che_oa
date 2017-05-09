<?php
namespace app\modules\oa_v1\controllers;


use Yii;
use app\modules\oa_v1\models\BaoxiaoForm;
use yii\web\UploadedFile;


class BaoxiaoController extends BaseController
{
	/**
	 * 报效申请
	 */
	public function actionAdd()
	{
		$file_config = [
			'fujian'=>'/upload/files/baoxiao/'.date('Ymd'),
			'pic'=>'/upload/images/baoxiao/'.date('Ymd'),
			'pdf'=>'/upload/account/baoxiao/'.date('Ymd'),
		];
		
		$request = Yii::$app -> request;
		$post['BaoxiaoForm'] = Yii::$app->request->post();
		if($request->isPost)
		{
			$model = new BaoxiaoForm();
			$model -> load($post);
			$model -> title = $this->arrPersonInfo['person_name'].'的报销单';
			$model -> create_time = time();
			$model -> user = array('id'=>$this->arrPersonInfo['person_id'],'name'=>$this->arrPersonInfo['person_name']);;
			$model -> fujian  = $model -> saveFile(UploadedFile::getInstancesByName('fujian'),$file_config['fujian']);
			$model -> pic  = $model -> saveFile(UploadedFile::getInstancesByName('pic'),$file_config['pic']);
			
			if($model -> validate()){
				if($apply_id = $model -> saveBaoxiao() ){
					$model -> saveAccount($file_config['pdf']);
					return $this -> _return($apply_id,200);
				}else{
					return $this -> _return(null,404);
				}
			}else{
				return $this -> _return(current($model -> getErrors())[0],403);
			}
		}
	}
	
	public function actionGetType()
	{
		$data = [
				['id'=>1,'val'=>'住宿费'],
				['id'=>2,'val'=>'餐饮费'],
				['id'=>3,'val'=>'交通费'],
				['id'=>4,'val'=>'其他费'],
		];
		return $this -> _return(json_encode($data,JSON_UNESCAPED_UNICODE),200);
	}
	
	public function actionGetBankcard()
	{
		$data = [
			['card_id'=>'1234567890123456','bank_name'=>'工商银行'],
			['card_id'=>'2234567890123456','bank_name'=>'交通银行'],
		];
		return $this -> _return(json_encode($data,JSON_UNESCAPED_UNICODE),200);
	}
	
	public function actionGetUserList()
	{
		$data = [
			['person_id'=>10001,'person'=>'测试1','department'=>'万剩伟业 技术部'],
			['person_id'=>10005,'person'=>'测试5','department'=>'车城伟业 技术部'],
			['person_id'=>10002,'person'=>'测试2','department'=>'万剩伟业 新镇部'],
			['person_id'=>10003,'person'=>'测试3','department'=>'万剩伟业 总经理办公室'],
			['person_id'=>10009,'person'=>'测试9','department'=>'万剩伟业 技术部'],
			['person_id'=>10008,'person'=>'测试8','department'=>'万剩伟业 技术部'],
			['person_id'=>10010,'person'=>'测试10','department'=>'万剩伟业 技术部'],
		];
		return $this -> _return(json_encode($data,JSON_UNESCAPED_UNICODE),200);
	}
}