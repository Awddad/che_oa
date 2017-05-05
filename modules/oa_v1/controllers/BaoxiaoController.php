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
	private function _makeArray()
	{
		$test = array(
				'bank_card_id'=>'1234567890123456',
				'bank_name'=>'11111',
				'bank_name_des'=>'飞虎队',
				'bao_xiao_list'=>array(
						0=>array(
								'money'=>'12.02',
								'type_name'=>'伙食费',
								'type'=>'1',
								'des'=>'吃吃喝喝'
						),
						1=>array(
								'money'=>'300.50',
								'type_name'=>'住宿费',
								'type'=>'2',
								'des'=>'睡睡'
						),
				),
				'approval_persons'=>array(
						1=>array(
								'person_id'=>'1099',
								'person_name'=>'网三',
								'steep'=>'1',
						),
						2=>array(
								'person_id'=>'22',
								'person_name'=>'屌哥',
								'steep'=>'2',
						),
				),
				'copy_person'=>array(
						1=>array(
								'person_id'=>'1',
								'person_name'=>'dd',
						),
				),
		);
		echo http_build_query($test);die();
		return $test;
	}
	
	
}