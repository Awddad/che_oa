<?php
namespace app\commands;
/**
 * @功能：与权限系统交互的功能
 * @作者：王雕
 * @创建时间：2017-05-04
 */
use Yii;
use app\models\Org;
use app\models\Person;
use app\commands\PublicMethod;
class QuanXianLogic 
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
        ];
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
        $jsonRtn = $this->thisHttpPost($this->arrApiUrl['login'], $arrPost);
        $arrRtn = json_decode($jsonRtn, true);
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
        $jsonRtn = $this->thisHttpPost($this->arrApiUrl['organizations'], $arrPost);
        $arrRtn = json_decode($jsonRtn, true);
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
                'org_short_name' => $val['short_name'],
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
        $jsonRtn = $this->thisHttpPost($this->arrApiUrl['userlist'], $arrPost);
        $arrRtn = json_decode($jsonRtn, true);
        if( $arrRtn['success'] == 1 && is_array($arrRtn['data']) && !empty($arrRtn['data']) &&!empty($arrRtn['data']['data']))//接口处理数据成功
        {
            //获取组织架构信息
            $arrOrgListTmp = Org::find()->select('*')->asArray()->all();
            foreach($arrOrgListTmp as $val)
            {
                $arrOrgList[$val['org_id']] = $val['org_name'];
            }
            //构造入库数据
            $arrPerson = [];
            foreach($arrRtn['data']['data'] as $val)
            {
                $arrPerson[] = [
                    'person_id' => $val['id'],
                    'person_name' => $val['name'],
                    'org_id' => $val['organization_id'],
                    'org_name' => (isset($arrOrgList[$val['organization_id']]) ? $arrOrgList[$val['organization_id']] : ''),
                    'is_delete' => ($val['status'] == 1 ? 0 : 1),
                    'profession' => $val['position_name'],
                    'email' => $val['email'],
                    'phone' => $val['phone'],
                    'bqq_open_id' => $val['bqq_open_id'],
                    'role_ids' => implode(',', array_map(function($v){return $v['id'];}, (array)$val['roles'] ))
                ];
            }
            //更新入库
            $strTable = Person::tableName();
            $arrKeys = array_keys($arrPerson[0]);
            $strSql = $this->createReplaceSql($strTable, $arrKeys, $arrPerson, 'person_id');
            $result = Yii::$app->db->createCommand($strSql)->execute();
            return $result;
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
    private function thisHttpPost($url, $params = array(), $options = array())
    {
        $jsonRtn = PublicMethod::http_post($url, $params, $options);
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




