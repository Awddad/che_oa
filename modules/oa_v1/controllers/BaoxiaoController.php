<?php
namespace app\modules\oa_v1\controllers;


use Yii;
use app\modules\oa_v1\models\BaoxiaoForm;


class BaoxiaoController extends BaseController
{
	/**
	 * 报效申请
	 */
	public function actionAdd()
	{
		$request = Yii::$app -> request;
		if($request->isPost)
		{
			$post['BaoxiaoForm'] = Yii::$app->request->post();
			$model = new BaoxiaoForm();
			$model -> load($post);
			$model -> user = array('id'=>$this->arrPersonInfo['person_id'],'name'=>$this->arrPersonInfo['person_name'],'org_id'=>$this->arrPersonInfo['org_id']);
			
			if($model -> validate()){
				if($apply_id = $model -> saveBaoxiao($this->arrPersonInfo) ){
					return $this -> _return($apply_id,200);
				}else{
					return $this -> _return($model -> getErrors(),404);
				}
			}else{
				return $this -> _return(current($model -> getErrors())[0],403);
			}
		}
		return $this -> _return(null,400);
	}
	
	
}