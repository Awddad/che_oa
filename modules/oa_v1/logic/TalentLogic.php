<?php
namespace app\modules\oa_v1\logic;

use app\models\TalentLog;
use yii;

class TalentLogic extends BaseLogic
{
	
	
	
	
	/**
	 * 人才表操作日志
	 * @param int $talent_id  人才id
	 * @param string $content 操作说明
	 * @param string $data 操作数据
	 * @param string $person_name 操作者
	 * @param int $person_id 操作者id
	 */
	public function addLog($talent_id=0,$content='',$data='',$person_name='',$person_id=0)
	{
		$model = new TalentLog();
		$model->talent_id = $talent_id;
		$model->content = $content;
		$model->data = is_array($data) ? json_encode($data,JSON_UNESCAPED_UNICODE) : $data;
		$model->created_at = time();
		$model->person_name = $person_name;
		$model->person_id = $person_id;
		try{
			if(!$model->insert()){
			    throw new \Exception('error');
			}
		}catch (\Exception $e){
			yii::info("人才日志错误 {$person_name} {$content} {$model->data}");
		}catch (\Throwable $e){
			yii::info("人才日志错误 {$person_name} {$content} {$model->data}");
		}
	}
}