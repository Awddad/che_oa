<?php
namespace app\modules\oa_v1\logic;

use yii;
use app\models\SalaryLog;

class SalaryLogic extends BaseLogic
{
	
	
	
	
	/**
	 * 薪酬导入日志
	 * @param string $data 
	 * @param int $person_id 操作者id 
	 * @param string $person_name 操作者
	 */
	public function addLog($data='',$person_id=0,$person_name='')
	{
		$model = new SalaryLog();
		$model->data = is_array($data) ? json_encode($data,JSON_UNESCAPED_UNICODE) : $data;
		$model->create_date = date('Y-m-d H:i:s',time());
		$model->create_time = time();
		$model->person_name = $person_name;
		$model->person_id = $person_id;
		try{
			if(!$model->insert()){
			    throw new \Exception('error');
			}
		}catch (\Exception $e){
			yii::info("薪酬导入日志错误 {$person_name} {$model->data}");
		}catch (\Throwable $e){
			yii::info("薪酬导入日志错误 {$person_name} {$model->data}");
		}
	}
}