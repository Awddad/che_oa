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
use Overtrue\Pinyin\Pinyin;
use yii\helpers\ArrayHelper;
use app\models\EmployeeAccount;

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
            'all_user' => $this->preUrl . '/users',//组织架构下所有人
            'add_user' => $this->preUrl . '/users/create',//添加用户
            'update_user' => $this->preUrl . '/users/%d/update',//修改用户
            'delete_user' => $this->preUrl . '/users/%d/delete',//删除用户
            'user_detail' => $this->preUrl . '/users/detail',//用户详情
            'user_del_bankcards' => $this->preUrl .'/users/bankcards',//删除银行卡
            'qq_user' => $this->preUrl .'/bqq/users/%s',//通过姓名或QQ帐号获取qq用户信息
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
        ];
        $arrRtn = $this->thisHttpPost($this->arrApiUrl['menu_list'].'?per_page= 100000&page=1', $arrPost);
        if($arrRtn['success'] && !empty($arrRtn['data']) && isset($arrRtn['data']['data']))
        {
            $arrInsert = [];
            $arrExistSlug = [];
            foreach($arrRtn['data']['data'] as $val)
            {
                //判断权限以slug别名为主，别名相同及为同一个功能，所以别名出现多次的时候只存一次
                //if(!in_array($val['slug'], $arrExistSlug))
                //{
                    $arrInsert[] = [
                        'id' => $val['id'],
                        'slug' => $val['slug'],
                        'name' => $val['name'],
                        'url' => $val['url'],
                    ];
                    //$arrExistSlug[] = $val['slug'];
                //}
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
                'manager' => $val['person_in_charge'] ?: 0,
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
        $arrRtn = $this->httpGet($this->arrApiUrl['userlist'].'?'.http_build_query($arrPost));
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
            $arrBankList = [];//oa_person_bank_info表的入库数据
            foreach($arrRtn['data']['data'] as $val)
            {
                $arrPerson[] = [
                    'person_id' => $val['id'],
                    'person_name' => $val['name'],
                    'org_id' => $val['organization_id'],
                    'org_name' => (isset($arrOrgList[$val['organization_id']]) ? $arrOrgList[$val['organization_id']] : ''),
                    'org_full_name' => $this->getOrgFullName($val['organization_id'], $arrOrgListTmp),
                    'is_delete' => ($val['deleted_at'] ? 1 : 0),
                    'profession' => $val['position_name'],
                    'email' => $val['email'],
                    'phone' => $val['phone'],
                    'bqq_open_id' => $val['bqq_open_id'],
                    'role_ids' => isset($val['roles']) ? implode(',', array_map(function($v){return $v['id'];}, (array)$val['roles'] )):'',
                    'company_id' => $this->getCompanyId($val['organization_id'], $arrOrgListTmp)
                ];
                //银行卡信息
                /*
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
                */
            }
            //更新入库 - oa_person 表
            Person::updateAll(['is_delete'=>1],['is_delete'=>0]);
            $strTable = Person::tableName();
            $arrKeys = array_keys($arrPerson[0]);
            $strSql = $this->createReplaceSql($strTable, $arrKeys, $arrPerson, 'person_id');
            $result = Yii::$app->db->createCommand($strSql)->execute();

            //更新入库 - oa_person_bank_info表
            /* 银行卡信息OA自己维护 2017-07-04
            $strTable = PersonBankInfo::tableName();
            $arrKeys = array_keys($arrBankList[0]);
            $strSql = $this->createReplaceSql($strTable, $arrKeys, $arrBankList, 'id');
            $result = Yii::$app->db->createCommand($strSql)->execute(); 
            */
            /*
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
            */
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
            $arrOrgListTmp = $this->getAllOrg();
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
        if(count($arrOrgName) > 1) {
            array_shift($arrOrgName);
        }
        $strOrgFullName = implode('-', $arrOrgName);
        return $strOrgFullName;
    }
    
    public function getAllOrg()
    {
        $cache = Yii::$app->cache;
        $arrOrgListTmp = $cache->get("ALL_ORG_CHE");
        if(empty($arrOrgListTmp)) {
            $arrOrgListTmp = Org::find()->select('*')->asArray()->all();
            $cache->set("ALL_ORG_CHE", $arrOrgListTmp, 600);
        }
        return $arrOrgListTmp;
    }
    
    /**
     * 得到公司ID
     *
     * @param $orgId
     * @param array $arrOrgListTmp
     * @return int|mixed
     */
    public function getCompanyId($orgId, $arrOrgListTmp = [])
    {
        if(empty($arrOrgListTmp))
        {
            $arrOrgListTmp = $this->getAllOrg();
        }
        foreach($arrOrgListTmp as $val)
        {
            $arrOrgList[$val['org_id']] = $val;
        }
        
        $arrOrgIds = [];
        while(isset($arrOrgList[$orgId]))
        {
            array_unshift($arrOrgIds, $arrOrgList[$orgId]['org_id']);
            $orgId = $arrOrgList[$orgId]['pid'];
        }
        if(empty($arrOrgIds)) {
            return 0;
        }
        
        if(count($arrOrgIds) > 1) {
            return $arrOrgIds[1];
        }
        return $arrOrgIds[0];
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
                    'org_ids' => implode(',', $val['organization_ids']),
                    'company_ids' => $this->getCompanyIds($val['organization_ids']),
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
     * 公司IDS
     * @param $orgIds
     *
     * @return string
     */
    public function getCompanyIds($orgIds)
    {
        $data = [];
        foreach ($orgIds as $v){
            $companyId = $this->getCompanyId($v);
            if (in_array($companyId, $data) || $companyId == 0) {
                continue;
            }
            $data[] = $companyId;
        }
        return implode(',', $data);
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
    /**
     * @功能：从权限系统中获取职位列表
     * @作者：yjr
     */
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
    				'short_name' => $v['slug'],
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
     * @功能：与权限系统交互，获取所有员工信息列表
     * @作者：yjr
     * @创建时间：2017-06-22
     */
    public function curlUpdateAllUser()
    {
    	$arrPost = [
    			'_token' => $this->_token,
    			'page' => 1,
    			'per_page' => 1000000,//一次拉取所有员工
    			'show_deleted' => 1,
    			//'show_bank_cards' => 1,
    	];
    	$url = $this->arrApiUrl['all_user'].'?'.http_build_query($arrPost);
    	$arrRtn = $this->httpGet($url);
    	if( $arrRtn['success'] == 1 && is_array($arrRtn['data']) && !empty($arrRtn['data']) &&!empty($arrRtn['data']['data']))//接口处理数据成功
    	{
    		//获取组织架构信息
    		$arrOrgListTmp = Org::find()->select('*')->asArray()->all();
    		foreach($arrOrgListTmp as $val)
    		{
    			$arrOrgList[$val['org_id']] = $val['org_name'];
            }
            // 构造入库数据
            $arrPerson = [];//oa_person表的入库数据
            $arrEmployee = []; // oa_employee表的入库数据
            $arrBankList = []; // oa_person_bank_info表的入库数据
            $arrAccount = []; // oa_employee_account表的入库数据
            foreach ($arrRtn['data']['data'] as $val) {
                $arrEmployee[] = [
                    'person_id' => $val['id'],
                    'name' => $val['name'],
                    'org_id' => $val['organization_id'],
                    'profession' => $val['position_id'],
                    'phone' => $val['phone'],
                    'email' => $val['email'],
                    'status' => $val['deleted_at'] ? 3 :2,
                    'employee_type' => EmployeeType::find()->where(['slug' => 'shiyong'])->one()->id,
                    'leave_time' => $val['deleted_at'] ? date('Y-m-d', strtotime($val['deleted_at'])) : '',
                ];
                $val['bqq_account'] && $arrAccount[$val['id']] = [
                    'qq' => $val['bqq_account']
                ];
                if($val['deleted_at']){
                    $arrPerson[] = $val['id'];
                }
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
    		//处理arrEmployee
    		$tmp_employee  = Employee::find()->select('id,person_id')->where(['>','person_id',0])->all();
    		$employee = [];
    		foreach($tmp_employee as $v){
    		    $employee[$v['person_id']] = $v['id'];
    		}
    		foreach($arrEmployee as $k=>$v){
    		    $arrEmployee[$k]['id'] = isset($employee[$v['person_id']])?$employee[$v['person_id']]:null;
    		}
    		//更新入库 - oa_employee
    		$strTable = Employee::tableName();
    		$arrKeys = array_keys($arrEmployee[0]);
    		$strSql = $this->createReplaceSql($strTable, $arrKeys, $arrEmployee, 'id',[/*'status',*/'employee_type','phone','email']);
    		$result= Yii::$app->db->createCommand($strSql)->execute();

    		//更新 - oa_person
    		if ($arrPerson) {
                $result += Person::updateAll(['is_delete' => 1], ['in','person_id',$arrPerson]);
            }
            //为arrAcount附上employee_id
            if($arrAccount){
                $tmp_employee  = Employee::find()->select('id,person_id')->where(['>','person_id',0])->all();
        		foreach($tmp_employee as $v){
        		    isset($arrAccount[$v['person_id']]) && $arrAccount[$v['person_id']]['employee_id'] = $v['id'];
        		}
    		    $strTable = EmployeeAccount::tableName();
    		    $arrKeys = array_keys(current($arrAccount));
        		$strSql = $this->createReplaceSql($strTable, $arrKeys, $arrAccount, 'id');
        		$result += Yii::$app->db->createCommand($strSql)->execute();
            }
            
            // 更新入库 - oa_person_bank_info表
            /* 银行卡信息OA自己维护 2017-07-04 
            $strTable = PersonBankInfo::tableName();
            $arrKeys = array_keys($arrBankList[0]);
            $strSql = $this->createReplaceSql($strTable, $arrKeys, $arrBankList, 'id');
            Yii::$app->db->createCommand($strSql)->execute(); 
            */
            
    		return $result;
    	}
    	return false;
    }
    
    /**
     * 与权限系统交互 添加用户
     * @param array $params  [name,email,roles_id,org_id,phone,position_id]
     */
    public function curlAddUser($params)
    {
    	$pinyin = new Pinyin();
    	$password = implode('', $pinyin->name($params['name']));
    	
    	$arrPost = [
    			'_token' => $this->_token,
    			'name' => $params['name'],
    			'email' => $params['email'],
    			'password' => $password,
    			'password_confirmation' => $password,
    			'roles' => [$params['roles']],
    			'organization_id' => $params['org_id'],
    			'phone' => $params['phone'],
    			'position_id' => $params['position_id'],
    	        'bqq_account' => $params['qq'],
    	];
    	$arrRtn = $this->thisHttpPost($this->arrApiUrl['add_user'], $arrPost);
    	if( $arrRtn['success'] == 1 && is_array($arrRtn['data']) && $arrRtn['data']['id'] > 0)//接口处理数据成功
    	{
    		return ['status'=>true,'id'=>$arrRtn['data']['id']];
    	}
    	return ['status'=>false,'msg'=>$arrRtn['message']];
    }
    
    /**
     * 与权限系统交互 删除用户
     * @param int $person_id (oa_person表的id)
     * @return boolean
     */
    public function curlDeleteUser($person_id)
    {
    	$arrPost = [
    			'_token' => $this->_token
    	];
    	$url = sprintf($this->arrApiUrl['delete_user'],$person_id);
    	$arrRtn = $this->thisHttpPost($url, $arrPost);
    	if( $arrRtn['success'] == 1)//接口处理数据成功
    	{
    		return ['status'=>true];
    	}
    	return ['status'=>false,'msg'=>$arrRtn['message']];
    }
    
    /**
     * 与权限系统交互 修改用户
     * @param array $params [name,email,org_id,position_id,bank_cards]
     * @return boolean
     */
    public function curlEditUser($params)
    {
    	$arrPost = [
    			'_token' => $this->_token,
    			'name' => $params['name'],
    			'email' => $params['email'],
    	];
    	isset($params['org_id']) && $arrPost['organization_id'] = $params['org_id'];
    	isset($params['position_id']) && $arrPost['position_id'] = $params['position_id'];
    	isset($params['bank_cards']) && $arrPost['bank_cards'] = $params['bank_cards'];
    	isset($params['phone']) && $arrPost['phone'] = $params['phone'];
    	
    	$url = sprintf($this->arrApiUrl['update_user'],$params['person_id']);
    	$arrRtn = $this->thisHttpPost($url, $arrPost);
    	if( $arrRtn['success'] == 1)//接口处理数据成功
    	{
    		return ['status'=>true];
    	}
    	return ['status'=>false,'msg'=>$arrRtn['message']];
    }
    /**
     * 与权限系统交互 修改用户（拉取权限）
     * @param array $params [name,email,org_id,position_id,bank_cards]
     * @return boolean
     */
    public function curlEditUser2($params)
    {
        $userDetail = $this->curlGetUserDetail($params['person_id'], ['show_project_roles'=>1]);
        $roles = [];
        foreach($userDetail['project_roles'] as $v){
            $roles[] = $v['role_id'];
        }
        $arrPost = [
            '_token' => $this->_token,
            'name' => $params['name'],
            'email' => $params['email'],
            'bqq_account' => $params['qq'],
            'roles' => $roles,
        ];
        isset($params['org_id']) && $arrPost['organization_id'] = $params['org_id'];
        isset($params['position_id']) && $arrPost['position_id'] = $params['position_id'];
        isset($params['bank_cards']) && $arrPost['bank_cards'] = $params['bank_cards'];
        isset($params['phone']) && $arrPost['phone'] = $params['phone'];
        //isset($params['qq']) && $arrPost['bqq_open_id'] = $params['qq'];
         
        $url = sprintf($this->arrApiUrl['update_user'],$params['person_id']);
        $arrRtn = $this->thisHttpPost($url, $arrPost);
        if( $arrRtn['success'] == 1)//接口处理数据成功
        {
            return ['status'=>true];
    	}
    	return ['status'=>false,'msg'=>$arrRtn['message']];
    }
    
    /**
     * 与权限系统交互 删除银行卡
     * @param int $user_id 用户id
     * @param int $bank_id 银行卡id
     * @return boolean
     */
    public function curlDelBank($user_id,$bank_id)
    {
        $arrPost = [
            '_token' => $this->_token,
            'user_id' => $user_id,
            'bank_id' => $bank_id,
        ];
        $arrRtn = $this->thisHttpPost($this->arrApiUrl['user_del_bankcards'], $arrPost);
        if( $arrRtn['success'] == 1)//接口处理数据成功
        {
            $model = PersonBankInfo::findOne($bank_id);
            if($model){
                $model->delete();
            }
            return true;
        }
        return false;
    }
    
    
    
    public function curlGetQQUserInfo($name)
    {
        $arrGet = [
            '_token' => $this->_token,
        ];
        $url = sprintf($this->arrApiUrl['qq_user'],$name).'?'.http_build_query($arrGet);
        $arrRtn = $this->httpGet($url);
        
        if($arrRtn['success'] == 1 && is_array($arrRtn['data'])){
            return ['status'=>true,'data'=>$arrRtn['data']];
        }
        return ['status'=>false,'msg'=>$arrRtn['message']];
    }
    
    /**
     * 脚本：为权限系统所有用户添加OA普通员工权限
     */
    public function addOaRoles()
    {
        return true;
        //拉取所有员工
        $arrPost = [
            '_token' => $this->_token,
            'page' => 1,
            'per_page' => 100000//一次拉取所有员工
        ];
        $arrRtn = $this->thisHttpPost($this->arrApiUrl['all_user'], $arrPost);
        if( $arrRtn['success'] == 1 && is_array($arrRtn['data']) && !empty($arrRtn['data']) &&!empty($arrRtn['data']['data']))//接口处理数据成功
        {
            //OA普通员工权限
            $role = Role::find()->where(['slug'=>'yuangong'])->one();
            
            foreach($arrRtn['data']['data'] as $val)
            {
                //获取员工详情（主要是权限）
                $userDetail = $this->curlGetUserDetail($val['id'], ['show_project_roles'=>1]);
                
                if($userDetail){
                    foreach($userDetail['project_roles'] as $v){
                        $roles[] = $v['role_id'];
                    }
                    $roles[] = $role['id'];//给用户添加OA普通员工权限
                    
                    $arrPost = [
                        '_token' => $this->_token,
                        'name' => $userDetail['name'],
                        'email' => $userDetail['email'],
                        'roles' => $roles,
                    ];
                    $url = sprintf($this->arrApiUrl['update_user'],(int)$userDetail['id']);
                    $res = $this->thisHttpPost($url, $arrPost);
                    unset($arrPost);
                    unset($roles);
                }
            }
            return true;
        }
        return false;
    }
    
    
    /**
     * 与权限系统交互 获取用户详情
     * @param int $user_id  用户id
     * @param array $params  参数 [show_project_roles，show_bank_cards]
     * @return array
     */
    public function curlGetUserDetail($user_id,$params)
    {
        $arrGet = [
            '_token' => $this->_token,
            'id' => $user_id,
            'show_project_roles' => isset($params['show_project_roles']) ? $params['show_project_roles'] :0,
            'show_bank_cards' => isset($params['show_bank_cards']) ? $params['show_bank_cards'] :0,
        ];
        $url = $this->arrApiUrl['user_detail'].'?'.http_build_query($arrGet);
        $arrRtn = $this->httpGet($url);
        
        if($arrRtn['success'] == 1 && is_array($arrRtn['data'])){
            return $arrRtn['data'];
        }
        return false;
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
     * @param array		$arrNotUpdate	不更新字段
     * @return string   $strRtn         sql语句
     */
    private function createReplaceSql($strTable, $arrKeys, $arrData, $strPrimaryKey,$arrNotUpdate=[])
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
            if($key != $strPrimaryKey && !in_array($key, $arrNotUpdate))
            {
                $strSQL .= "$key=VALUES({$key}),";
            }
        }
        $strRtn = substr($strSQL, 0, -1);
        return $strRtn;
    }
}




