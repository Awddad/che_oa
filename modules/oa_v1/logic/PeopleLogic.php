<?php
namespace app\modules\oa_v1\logic;

use app\models\PeopleLog;
use yii;
use yii\db\Exception;

class PeopleLogic extends BaseLogic
{
    /**
     * 添加日志
     * @param number $talent_id 人才id
     * @param number $employee_id 员工id
     * @param string $content 操作说明
     * @param string $data 操作数据
     * @param number $person_id 操作者id
     * @param string $person_name 操作证名字
     * @throws \Exception
     */
    public function addLog($talent_id=0,$employee_id=0,$content='',$data='',$person_id=0,$person_name='')
    {
        $model = new PeopleLog();
        $model->talent_id = $talent_id?:0;
        $model->employee_id = $employee_id?:0;
        $model->content = $content;
        $model->data = is_array($data) ? json_encode($data,JSON_UNESCAPED_UNICODE) : $data;
        $model->person_id = $person_id;
        $model->person_name = $person_name;
        $model->create_at = time();
        try{
			if(!$model->insert()){
			    throw new Exception('error');
			}
		}catch (\Exception $e){
			yii::info("人才详情日志错误 {$person_name} {$content} {$model->data}");
		}catch (\Throwable $e){
			yii::info("人才详情日志错误 {$person_name} {$content} {$model->data}");
		}
    }
}