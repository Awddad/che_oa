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
		if($request->isPost)
		{
			$post['BaoxiaoForm'] = Yii::$app->request->post();
			$model = new BaoxiaoForm();
			$model -> load($post);
			$model -> title = $model -> createApplyTitle($this->arrPersonInfo);
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
		return $this -> _return(null,400);
	}
	
	public function actionGetType()
	{
		$data = [
				['id'=>1,'val'=>'住宿费'],
				['id'=>2,'val'=>'餐饮费'],
				['id'=>3,'val'=>'交通费'],
				['id'=>4,'val'=>'其他费'],
		];
		return $this -> _return($data,200);
	}
	
	public function actionGetBankcard()
	{
		$data = [
			['card_id'=>'1234567890123456','bank_name'=>'工商银行','bank_des'=>'普陀分行'],
			['card_id'=>'2234567890123456','bank_name'=>'交通银行','bank_des'=>'世界支行'],
		];
		return $this -> _return($data,200);
	}
	
	public function actionGetUserList()
	{
		$data = [
			['person_id'=>257,'person'=>'测试1','department'=>'万剩伟业 技术部'],
			['person_id'=>270,'person'=>'测试5','department'=>'车城伟业 技术部'],
			['person_id'=>271,'person'=>'测试2','department'=>'万剩伟业 新镇部'],
			['person_id'=>272,'person'=>'测试3','department'=>'万剩伟业 总经理办公室'],
			['person_id'=>274,'person'=>'测试9','department'=>'万剩伟业 技术部'],
			['person_id'=>275,'person'=>'测试8','department'=>'万剩伟业 技术部'],
		];
		return $this -> _return($data,200);
	}
	
}