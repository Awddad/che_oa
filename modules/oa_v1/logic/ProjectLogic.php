<?php
/**
 * Created by PhpStorm.
 * User: YJR
 * Date: 2017/9/1
 * Time: 16:00
 */
namespace app\modules\oa_v1\logic;


use app\logic\Logic;
use app\models\ApprovalConfig;
use app\models\Org;
use yii\helpers\ArrayHelper;

/**
 * 项目相关
 *
 * Class RoleLogic
 * @package app\modules\oa_v1\logic
 */
class ProjectLogic extends Logic
{
    public function getProjects($org_id = 0)
    {
        $projects = $this->getAllProjects();
        if($org_id > 0){
            /**
             * @var $model ApprovalConfig
             */
            $model = null;
            while(!$model){
                $model = ApprovalConfig::find()->where(['org_id'=>$org_id,'apply_type'=>16])->orderBy('updated_at desc')->one();
                if(!$model && $org_id >= 1){
                    $org = Org::findOne($org_id);
                    $org_id = $org->pid;
                    continue;
                }
                break;
            }
            if($model) {
                $config = json_decode($model->approval,1);
                $keys = array_keys($config);
                $projects = ArrayHelper::index($projects, 'id');
                foreach($projects as $k=>$v){
                    if(in_array($k,$keys)){
                        unset($projects[$k]);
                    }
                }
                $projects = array_values($projects);
            }
        }
        return $projects;


    }

    public function getAllProjects()
    {
        return [
            [
                'id'=>1,
                'project_name'=>'ERP系统',
            ],
            [
                'id'=>2,
                'project_name'=>'CRM系统',
            ],
            [
                'id'=>3,
                'project_name'=>'电商后台'
            ],
            [
                'id'=>4,
                'project_name'=>'OA系统'
            ],
            [
                'id'=>5,
                'project_name'=>'用户中心'
            ],
            [
                'id'=>6,
                'project_name'=>'ERP批销'
            ],
            [
                'id'=>7,
                'project_name'=>'财务系统'
            ],
        ];
    }
}