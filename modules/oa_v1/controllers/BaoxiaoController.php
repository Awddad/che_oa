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
		$user = array('id'=>228453,'name'=>'三屌');
		
		$file_config = [
			'fujian'=>'/upload/files/baoxiao/'.date('Ymd'),
			'pic'=>'/upload/images/baoxiao/'.date('Ymd')
		];
		
		$request = Yii::$app -> request;
		$post['BaoxiaoForm'] = Yii::$app->request->post();
		if($request->isPost)
		{
			$model = new BaoxiaoForm();
			$model -> load($post);
			$model -> user = $user;
			$model -> fujian  = $model -> saveFile(UploadedFile::getInstancesByName('fujian'),$file_config['fujian']);
			$model -> pic  = $model -> saveFile(UploadedFile::getInstancesByName('pic'),$file_config['pic']);
			
			if($model -> validate()){
				if($model -> saveBaoxiao()){
					return $this -> _return(null);
				}else{
					return $this -> _return(null,'system error',500);
				}
			}else{
				return $this -> _return(current($model -> getErrors()),'error',500);
			}
		}
	}
	
	
}