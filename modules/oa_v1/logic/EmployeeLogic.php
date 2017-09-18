<?php
namespace app\modules\oa_v1\logic;

use app\models\Role;
use app\logic\server\QuanXianServer;

class EmployeeLogic extends BaseLogic
{
    /**
     * 修改权限系统用户
     * @param \app\models\Employee $employee
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
            'qq' => $account ? $account->qq :'',//qq帐号
        ];
        //权限系统添加用户
        $objQx = new QuanXianServer();
        $res = $objQx->curlEditUser2($params);
        return $res;
    }
    /**
     * 添加权限系统用户
     * @param \app\models\Employee $employee
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
            'qq' => $employee->account->qq ? :'',//qq
        ];
        //权限系统添加用户
        $objQx = new QuanXianServer();
        $res = $objQx->curlAddUser($params);
        return $res;
    }
    /**
     * 删除权限系统用户
     * @param \app\models\Employee $employee
     * @return array
     */
    public function delQxEmp($employee)
    {
        //权限系统接口 删除用户
        $objQx = new QuanXianServer();
        return $objQx->curlDeleteUser($employee->person_id);
    }
    
    /**
     * 通过qq帐号 或者姓名 获取QQ用户信息
     * @param string $name
     */
    public function getQQUser($name)
    {
        //权限系统接口 获取qq用户
        $quanxian = new QuanXianServer();
	    return $quanxian->curlGetQQUserInfo($name);
    }
}
