<?php
namespace app\modules\oa_v1\logic;

use yii;
use app\models\ApprovalConfigLog;

class ApprovalConfigLogic extends BaseLogic
{
   
    /**
     * 审批流程操作日志
     * @param string $title 操作标题
     * @param number $config_id oa_approval_config 的id
     * @param string $org_name 公司名
     * @param string $apply_name 审批类型名
     * @param string $data 数据
     * @param number $person_id 操作证id
     * @param string $person_name 操作者名字
     */
    public function addLog($title='',$config_id = 0,$org_name='',$apply_name='',$data='',$person_id=0,$person_name='')
    {
        $model = new ApprovalConfigLog();
        $model->title = $title;
        $model->config_id = $config_id;
        $model->org_name = $org_name;
        $model->apply_name = $apply_name;
        $model->data = is_array($data) ? json_encode($data,JSON_UNESCAPED_UNICODE) : $data;
        $model->time = time();
        $model->person_name = $person_name;
        $model->person_id = $person_id;
        try{
            if(!$model->insert()){
                throw new yii\base\Exception('error');
            }
        }catch (yii\base\Exception $e){
            yii::info("审批流程操作日志错误 {$person_name} {$title} {$model->data}");
        }catch (\Throwable $e){
            yii::info("审批流程操作日志错误 {$person_name} {$title} {$model->data}");
        }
    }
}