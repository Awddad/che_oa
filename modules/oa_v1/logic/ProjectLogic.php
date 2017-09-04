<?php
/**
 * Created by PhpStorm.
 * User: YJR
 * Date: 2017/9/1
 * Time: 16:00
 */
namespace app\modules\oa_v1\logic;


use app\logic\Logic;

/**
 * 项目相关
 *
 * Class RoleLogic
 * @package app\modules\oa_v1\logic
 */
class ProjectLogic extends Logic
{
    public function getProjects()
    {
        return [
            [
                'id'=>1,
                'project_name'=>'CRM',
            ],
            [
                'id'=>2,
                'project_name'=>'ERP',
            ],
            [
                'id'=>3,
                'project_name'=>'OA'
            ]
        ];
    }
}