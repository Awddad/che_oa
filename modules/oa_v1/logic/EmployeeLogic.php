<?php
namespace app\modules\oa_v1\logic;

use app\models\Role;
use app\logic\server\QuanXianServer;

class EmployeeLogic extends BaseLogic
{
    /**
     * 修改权限系统用户
     * @param app\models\Employee $employee
     * @return array
     */
    public function editQxEmp($employee)
    {
        if(empty($employee) || !$employee->person_id){
            return ['status'=>false,'msg'=>'员工错误'];
        }
        $account = $employee->account;
        $params = [
            'person_id' => $employee->person_id,
            'name' => $employee->name,
            'email' => $account ? $account->email : $employee->email,
            'org_id'=> $employee->org_id, // 组织
            'position_id' => $employee->profession, // 职位
            'phone' => $account ? $account->tel : $employee->phone,// 电话
        ];
        //权限系统添加用户
        $objQx = new QuanXianServer();
        $res = $objQx->curlEditUser($params);
        return $res;
    }
    /**
     * 添加权限系统用户
     * @param app\models\Employee $employee
     * @return array
     */
    public function addQxEmp($employee)
    {
        if(empty($employee)){
            return ['status'=>false,'msg'=>'员工错误'];
        }
        $params = [
            'name' => $employee->name,
            'email' => $employee->account->email,
            'roles' => Role::findOne(['slug'=>'yuangong'])->id,//oa普通员工权限
            'org_id'=> $employee->org_id,//组织
            'position_id' => $employee->profession,//职位
            'phone' => $employee->account->tel ?: $employee->phone,//手机
        ];
        //权限系统添加用户
        $objQx = new QuanXianServer();
        $res = $objQx->curlAddUser($params);
        return $res;
    }
}
