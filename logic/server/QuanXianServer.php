<?php
namespace app\logic\server;
/**
 * @功能：与权限系统交互的功能
 * @作者：王雕
 * @创建时间：2017-05-04
 */
use Yii;
use app\models\Org;
use app\models\Person;
use app\models\PersonBankInfo;
use app\models\Role;
use app\models\RoleOrgPermission;
use app\models\Menu;
use app\models\Job;
use app\models\EmployeeType;
use app\models\Employee;
class QuanXianServer extends Server
{
    /*
     * @var string  与权限系统对接的接口地址（前面的公用部分）
     */
    private $preUrl = '';
    /*
     * @var string  项目token
     */
    private $_token = '';
    /*
     * @var array   保存接口地址
     */
    private $arrApiUrl = [];
    /**
     * @var array   静态成员变量，用来把接口中获取到的组织架构格式化用
     */
    private static $arrOrgList = [];
    
    /**
     * @功能：构造函数 - 初始化一些接口地址
     * @作者：王雕
     * @创建时间：2017-05-04
     */
    public function __construct(){
        $this->preUrl = Yii::$app->params['quan_xian']['auth_api_url'];
        $this->_token = Yii::$app->params['quan_xian']['auth_token'];
        //配置接口地址列表
        $this->arrApiUrl = [
            'organizations' =>  $this->preUrl . '/organizations/tree',//获取组织架构的接口地址
            'userlist' => $this->preUrl . '/projects/users',//获取项目中的所有人
            'login' => $this->preUrl . '/users/login', //登录接口
            'bqqtips' => $this->preUrl . '/bqq/tips',//企业QQ客户端提醒
            'roles' => $this->preUrl . '/projects/roles', //拉取项目中的角色以及角色权限的数据接口
            'role_user' => $this->preUrl . '/projects/role_user',//
            'user_add_bankcards' => $this->preUrl . '/users/bankcards',//用户添加银行卡
            'menu_list' => $this->preUrl . '/projects/permissions',//项目目录菜单
            'positions' => $this->preUrl . '/organizations/positions',//职位列表
        ];
    }
    
    /**
     * @功能：获取项目的目录列表缓存
     * @作者：王雕
     * @创建时间：2017-05-04
     */
    public function curlUpdateMenus()
    {
        // 数据存表
        $arrPost = [
            '_token' => $this->_token,
            'per_page' => 100000,
            'page' => 1,
        ];
        $arrRtn = $this->thisHttpPost($this->arrApiUrl['menu_list'], $arrPost);
        if($arrRtn['success'] && !empty($arrRtn['data']) && isset($arrRtn['data']['data']))
        {
            $arrInsert = [];
            $arrExistSlug = [];
            foreach($arrRtn['data']['data'] as $val)
            {
                //判断权限以slug别名为主，别名相同及为同一个功能，所以别名出现多次的时候只存一次
                if(!in_array($val['slug'], $arrExistSlug)) 
                {
                    $arrInsert[] = [
                        'id' => $val['id'],
                        'slug' => $val['slug'],
                        'name' => $val['name'],
                    ];
                    $arrExistSlug[] = $val['slug'];
                }
            }
            if($arrInsert)
            {
                $db = Yii::$app->db;
                $transaction = $db->beginTransaction();
                try
                {
                    $strTable = Menu::tableName();
                    $arrClumes = array_keys($arrInsert[0]);
                    //清表
                    $db->createCommand()->delete($strTable)->execute();
                    //入库
                    $db->createCommand()->batchInsert($strTable, $arrClumes, $arrInsert)->execute();
                    $transaction->commit();
                }
                catch (Exception $ex)
                {
                    $transaction->rollBack();
                    throw $ex;
                }
            }
        }
    }

    /**
     * @功能：通过api接口登录
     * @作者：王雕
     * @创建时间：2017-05-04
     * @param string $strPhoneOrEmail   用户名（手机号或者邮箱号）
     * @param string $strPwd            密码
     * @return array $arrLoginInfo      登录结果 result = 0 表示成功  非0 表示失败
     */
    public function curlLogin($strPhoneOrEmail, $strPwd)
    {
        $arrLoginInfo = [
            'result' => 1, //默认登录失败
            'access_token' => '',//登录成功后才有
            'person_id' => 0,//用户的 person_id 信息
        ];
        //通过用户名密码的形式api登录
        $arrPost = [
            '_token' => $this->_token,
            'account' => $strPhoneOrEmail,
            'password' => $strPwd,
        ];
        $arrRtn = $this->thisHttpPost($this->arrApiUrl['login'], $arrPost);
        //登录后需要返回权限列表
        if($arrRtn['success'] && !empty($arrRtn['data']))
        {
            $arrLoginInfo = [
                'result' => 0, //登录成功
                'access_token' => $arrRtn['data']['token'],//token
                'person_id' => $arrRtn['data']['id'],//用户的 person_id 信息
            ];
        }
        return $arrLoginInfo;
    }
    
    /**
     * @功能：拉取组织架构
     * @作者：王雕
     * @创建时间：2017-05-04
     * @throws \app\commands\Exception
     */
    public function curlUpdateOrg()
    {
        $arrPost = [
                '_token' => $this->_token,
                'organization_id' => 1, //车城控股集团 （总部）
                'show_users' => 0  //只返回架构数据，不返回人员信息
            ];
        $arrRtn = $this->thisHttpPost($this->arrApiUrl['organizations'], $arrPost);
        if( $arrRtn['success'] == 1 && is_array($arrRtn['data']) && !empty($arrRtn['data']) && !empty($arrRtn['data'][0]) )//接口处理数据成功
        {
            $this->formatOrgList($arrRtn['data']);
            //保存入库 - 清除旧的保存新的
            if(!empty(self::$arrOrgList))
            {
                $strTable = Org::tableName();//表名
                $arrClumes = array_keys(self::$arrOrgList[0]);//插入的列
                $db = Yii::$app->db;
                $transaction = $db->beginTransaction();
                try
                {
                    //清表
                    $db->createCommand()->delete($strTable)->execute();
                    //入库
                    $db->createCommand()->batchInsert($strTable,$arrClumes,self::$arrOrgList)->execute();
                    $transaction->commit();
                }
                catch (Exception $ex)
                {
                    $transaction->rollBack();
                    throw $ex;
                }
            }
        }
        return true;
    }
    
    /**
     * @功能：格式化组织架构信息
     * @作者：王雕
     * @创建时间：2017-05-04
     * @param array     $arrList    需要格式化的数据
     * @param int       $pid        该数据的父id
     */
    private function formatOrgList($arrList, $pid = 0)
    {
        foreach($arrList as $val)
        {
            self::$arrOrgList[] = [
                'org_id' => $val['id'],
                'org_name' => $val['name'],
                'org_short_name' => $val['short_name'] ? : '',
                'pid' => $pid,
            ];
            if(isset($val['children']) && is_array($val['children']) && !empty($val['children']))
            {
                $this->formatOrgList($val['children'], $val['id']);
            }
        }
    }
    
    
    /**
     * @功能：与权限系统交互，获取所有员工信息列表
     * @作者：王雕
     * @创建时间：2017-05-04
     */
    public function curlUpdateUser()
    {
        $arrPost = [
            '_token' => $this->_token,
            'page' => 1,
            'per_page' => 1000000,//一次拉取所有员工
            'show_deleted' => 1,
        ];
        $arrRtn = $this->thisHttpPost($this->arrApiUrl['userlist'], $arrPost);
        if( $arrRtn['success'] == 1 && is_array($arrRtn['data']) && !empty($arrRtn['data']) &&!empty($arrRtn['data']['data']))//接口处理数据成功
        {
            //获取组织架构信息
            $arrOrgListTmp = Org::find()->select('*')->asArray()->all();
            foreach($arrOrgListTmp as $val)
            {
                $arrOrgList[$val['org_id']] = $val['org_name'];
            }
            //构造入库数据
            $arrPerson = [];//oa_person表的入库数据
            $arrEmployee = [];//oa_employee表的入库数据
            $arrBankList = [];//oa_person_bank_info表的入库数据
            foreach($arrRtn['data']['data'] as $val)
            {
                $arrPerson[] = [
                    'person_id' => $val['id'],
                    'person_name' => $val['name'],
                    'org_id' => $val['organization_id'],
                    'org_name' => (isset($arrOrgList[$val['organization_id']]) ? $arrOrgList[$val['organization_id']] : ''),
                    'org_full_name' => $this->getOrgFullName($val['organization_id'], $arrOrgListTmp),
                    'is_delete' => ($val['status'] == 1 ? 0 : 1),
                    'profession' => $val['position_name'],
                    'email' => $val['email'],
                    'phone' => $val['phone'],
                    'bqq_open_id' => $val['bqq_open_id'],
                    'role_ids' => implode(',', array_map(function($v){return $v['id'];}, (array)$val['roles'] ))
                ];
                $arrEmployee[] = [
                		'person_id' => $val['id'],
                		'name' => $val['name'],
                		'org_id' => $val['organization_id'],
                		'job' => $val['position_id'],
                		'phone' => $val['phone'],
                		'email' => $val['email'],
                ];
                //银行卡信息
                if(isset($val['bank_cards']) && !empty($val['bank_cards']) && is_array($val['bank_cards']))
                {
                    foreach($val['bank_cards'] as $bankInfo)
                    {
                        $arrBankList[] = [
                            'id' => $bankInfo['id'],
                            'bank_name' => $bankInfo['bank'],
                            'bank_name_des' => '',
                            'bank_card_id' => $bankInfo['number'],
                            'is_salary' => $bankInfo['is_salary'],
                            'person_id' => $bankInfo['user_id'],
                        ];
                    }
                }
            }
            //更新入库 - oa_person 表
            $strTable = Person::tableName();
            $arrKeys = array_keys($arrPerson[0]);
            $strSql = $this->createReplaceSql($strTable, $arrKeys, $arrPerson, 'person_id');
            $result = Yii::$app->db->createCommand($strSql)->execute();
            
            //更新入库 - oa_employee
            $strTable = Employee::tableName();
            $arrKeys = array_keys($arrEmployee[0]);
            $strSql = $this->createReplaceSql($strTable, $arrKeys, $arrEmployee, 'id');
            Yii::$app->db->createCommand($strSql)->execute();
            
            
            //更新入库 - oa_person_bank_info表
            if(!empty($arrBankList))
            {
                $db = Yii::$app->db;
                $transaction = $db->beginTransaction();
                try
                {
                    $strBankTable = PersonBankInfo::tableName();
                    $arrBankClumes = array_keys($arrBankList[0]);
                    //清表
                    $db->createCommand()->delete($strBankTable)->execute();
                    //入库
                    $db->createCommand()->batchInsert($strBankTable, $arrBankClumes, $arrBankList)->execute();
                    $transaction->commit();
                } catch (Exception $ex) {
                    $transaction->rollBack();
                }
            }
            return $result;
        }
        return false;
    }
    
    /**
     * @功能：向权限系统增加员工银行卡
     * @作者：王雕
     * @创建时间：2017-05-15
     * @param int       $intPersonId            用户id
     * @param string    $strBankName            银行名
     * @param string    $strBankNameDes         支行名
     * @param string    $strCardId              卡号
     * @param int       $intIsSalary            是否是工资卡 1 - 是 非1 - 不是
     * @return boolean  $result                 true - 添加成功 false - 添加失败
     */
    public function curlAddUserBankList($intPersonId, $strBankName, $strBankNameDes, $strCardId, $intIsSalary = 0)
    {
        $result = false;
        $arrPost = [
            '_token' => $this->_token,
            'user_id' => $intPersonId,
            'bank' => $strBankName,
            'bank_detail' => $strBankNameDes,
            'number' => $strCardId,
            'is_salary' => ($intIsSalary == 1 ? 1 : 0)
        ];
        $arrRtn = $this->thisHttpPost($this->arrApiUrl['user_add_bankcards'], $arrPost);
        if($arrRtn['success'] == 1)//接口处理数据成功 - 加卡成功
        {
            //加卡成功 - 本地库中加银行卡 此处需要优化，直接插一条数据到银行表中就ok了，等栩栩提供成功后的id给我
            $this->curlUpdateUser();
            $result = true;
        }
        return $result;
    }
    
    
    /**
     * @功能：根据组织id获取组织架构全称
     * @作者：王雕
     * @创建时间：2017-05-11
     * @param string $orgId     需要查询的组织架构id
     * @param array $arrOrgListTmp  库里面组织架构全表信息，不传的话函数里面会自己查库获取
     * @return string $strOrgFullName   组织架构全称
     */
    public function getOrgFullName($orgId, $arrOrgListTmp = [])
    {
        if(empty($arrOrgListTmp))
        {
            $arrOrgListTmp = Org::find()->select('*')->asArray()->all();
        }
        foreach($arrOrgListTmp as $val)
        {
            $arrOrgList[$val['org_id']] = $val;
        }
        $arrOrgName = [];
        while(isset($arrOrgList[$orgId]))
        {
            array_unshift($arrOrgName, $arrOrgList[$orgId]['org_name']);
            $orgId = $arrOrgList[$orgId]['pid'];
        }
        $strOrgFullName = implode('-', $arrOrgName);
        return $strOrgFullName;
    }
    
    
    /**
     * @功能：从权限系统中拉取项目的角色配置信息
     * @作者：王雕
     * @创建时间：2017-05-11
     */
    public function curlUpdateRole()
    {
        $result = 0;
        $arrPost = [
            '_token' => $this->_token,
            'current_page' => 1,
            'per_page' => 1000000,//由于有分页，设置每页10万条数据一次拉取所有的 O(∩_∩)O~
        ];
        $arrRtn = $this->thisHttpPost($this->arrApiUrl['roles'], $arrPost);
        if($arrRtn['success'] == 1 && !empty($arrRtn['data']['data']) && is_array($arrRtn['data']['data']))
        {
            //整理入库数据
            $arrRoles = [];
            foreach($arrRtn['data']['data'] as $val)
            {
                $arrRoles[] = [
                    'id' => $val['id'],
                    'name' => $val['name'],
                    'slug' => $val['slug'],
                    'permissions' => json_encode($val['permissions'])
                ];
            }
            
            //更新入库 - oa_person_bank_info表
            if(!empty($arrRoles))
            {
                $db = Yii::$app->db;
                $transaction = $db->beginTransaction();
                try
                {
                    $strRoleTable = Role::tableName();
                    $arrRolesClumes = array_keys($arrRoles[0]);
                    //清表
                    $db->createCommand()->delete($strRoleTable)->execute();
                    //入库
                    $result = $db->createCommand()->batchInsert($strRoleTable, $arrRolesClumes, $arrRoles)->execute();
                    $transaction->commit();
                } catch (Exception $ex) {
                    $transaction->rollBack();
                }
            }
        }
        return $result;
    }
    
    
    /**
     * @功能：从权限系统中拉取项目的用户的角色和数据权限信息
     * @作者：王雕
     * @创建时间：2017-05-11
     */
    public function curlUpdateUserRoleOrgPermission()
    {
        $result = 0;
        $arrPost = [
            '_token' => $this->_token,
        ];
        $arrRtn = $this->thisHttpPost($this->arrApiUrl['role_user'], $arrPost);
        if($arrRtn['success'] == 1 && !empty($arrRtn['data']) && is_array($arrRtn['data']))
        {
            //整理入库数据
            $arrRoleData = [];
            foreach($arrRtn['data'] as $val)
            {
                $arrRoleData[] = [
                    'person_id' => $val['user_id'],
                    'role_id' => $val['project_role_id'],
                    'org_ids' => implode(',', $val['organization_ids'])
                ];
            }
            
            //更新入库 - oa_person_bank_info表
            if(!empty($arrRoleData))
            {
                $db = Yii::$app->db;
                $transaction = $db->beginTransaction();
                try
                {
                    $strTable = RoleOrgPermission::tableName();
                    $arrClumes = array_keys($arrRoleData[0]);
                    //清表
                    $db->createCommand()->delete($strTable)->execute();
                    //入库
                    $result = $db->createCommand()->batchInsert($strTable, $arrClumes, $arrRoleData)->execute();
                    $transaction->commit();
                } catch (Exception $ex) {
                    $transaction->rollBack();
                }
            }
        }
        return $result;
    }
    
    /**
     * @功能：与权限系统交互，给部分人的企业QQ发送tips消息
     * @作者：王雕
     * @创建时间：2017-05-04
     * @param string $strWindowsTitle   Tips弹出窗口的标题，限长24字符
     * @param string $strTipsTitle      Tips的消息标题，限长42字符
     * @param string $strContent        Tips的正文内容，限长264字符
     * @param array $arrReceivers       消息接收人，逗号分隔的open_id列表
     * @param string $strTipsUrl        点击消息跳转的网页地址如果有链接，使用此参数，url，限长1024字节
     * @param int $intShowTime          窗口显示的时间： 0 一直显示不会自动消失 大于0 显示相应时间后自动关闭（单位：秒），最大512秒
     * @return array $arrSendResult     发送结果 result = 0 成功， 非0 失败 msg 失败原因
     */
    public function sendbQQNotice($strWindowsTitle, $strTipsTitle, $strContent, $arrReceivers, $strTipsUrl = '', $intShowTime = 0)
    {
        $arrPost = [
            '_token' => $this->_token,
            'receivers' => implode(',', $arrReceivers),
            'window_title' => $strWindowsTitle,
            'tips_title' => $strTipsTitle,
            'tips_content' => $strContent,
        ];
        $strTipsUrl && $arrPost['tips_url'] = $strTipsUrl;
        $intShowTime && $arrPost['display_time'] = $intShowTime;
        if(mb_strlen($strWindowsTitle) > 24)
        {
            $arrSendResult = ['result' => 2, 'msg' => 'Tips弹出窗口的标题，限长24字符'];
        }
        else if(mb_strlen($strTipsTitle) > 42)
        {
            $arrSendResult = ['result' => 3, 'msg' => 'Tips的消息标题，限长42字符'];
        }
        else if(mb_strlen($strContent) > 264)
        {
            $arrSendResult = ['result' => 4, 'msg' => 'Tips的正文内容，限长264字符'];
        }
        else
        {
            $arrRtn = $this->thisHttpPost($this->arrApiUrl['bqqtips'], $arrPost);
            if($arrRtn && $arrRtn['success'] == 1)
            {
                $arrSendResult = ['result' => 0, 'msg' => ''];
            }
            else
            {
                $arrSendResult = ['result' => 5, 'msg' => (isset($arrRtn['message']) ? $arrRtn['message'] : 'api请求失败')];
            }
        }
        return $arrSendResult;
    }
    
    public function curlUpdatePositions()
    {
    	$arrPost = [
    			'_token' => $this->_token,
    			'show_deleted' => true
    	];
    	$arrRtn = $this->thisHttpPost($this->arrApiUrl['positions'], $arrPost);
    	if( $arrRtn['success'] == 1 && is_array($arrRtn['data']) && !empty($arrRtn['data']))//接口处理数据成功
    	{
    		//构造数据
    		$arrJob = [];//职位数据
    		foreach($arrRtn['data'] as $v){
    			$arrJob[] = [
    				'id' => $v['id'],
    				'name' => $v['name'],
    				'is_delete' => $v['deleted_at']==null ? 0 : 1,
    			];
    		}
    		//更新入库 - oa_person 表
    		$strTable = Job::tableName();
    		$arrKeys = array_keys($arrJob[0]);
    		$strSql = $this->createReplaceSql($strTable, $arrKeys, $arrJob, 'id');
    		$result = Yii::$app->db->createCommand($strSql)->execute();
    		return $result;
    	}
    }
    
    
    
    /**
     * @功能：将curl请求包一层，以便记录与权限系统的交互log
     * @作者：王雕
     * @创建时间：2017-05-04
     * @param string $url       url地址
     * @param array $params     post参数
     * @param array $options    curl配置参数
     * @return string $jsonRtn  curl请求结果
     */
    private function thisHttpPost($url, $params = array())
    {
        $jsonRtn = $this->httpPost($url, $params);
        /**
         * 可以加log
         */
        return $jsonRtn;
    }
    
    /**
     * @功能：拼接更新表数据的sql语句  使用replace into的形式
     * @作者：王雕
     * @创建时间：2017-05-04
     * @param string    $strTable       表明
     * @param array     $arrKeys        数据表的列名
     * @param array     $arrData        数据
     * @param string    $strPrimaryKey  表主键
     * @return string   $strRtn         sql语句
     */
    private function createReplaceSql($strTable, $arrKeys, $arrData, $strPrimaryKey)
    {
        $arrKeysNew = array_map(function(&$v){ return '`' . $v . '`';}, $arrKeys);//列名称处理一下
        $strKeys = implode(', ', $arrKeysNew);
        $arrStringValue = [];
        foreach($arrData as $val)
        {
            $val = array_map(function(&$v){return "'{$v}'";}, $val);
            $arrStringValue[] = '(' . implode(', ', $val) . ')';
        }
        $strValues = implode(', ', $arrStringValue);
        $strSQL = "INSERT INTO {$strTable} ({$strKeys}) values {$strValues} ON DUPLICATE KEY UPDATE ";
        foreach($arrKeys as $key)
        {
            if($key != $strPrimaryKey)
            {
                $strSQL .= "$key=VALUES({$key}),";
            }
        }
        $strRtn = substr($strSQL, 0, -1);
        return $strRtn;
    }
}




