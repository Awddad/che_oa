<?php
namespace app\modules\oa_v1\logic;

use yii;
use app\models\SalaryLog;
use app\models\Employee;

class SalaryLogic extends BaseLogic
{
    private $_seeKey = '7/p%^^&haha1>)999';
    private $_token_time = 10*60;
    
    /**
     * 通过密码获得token
     * @param arra $person 登入用户
     * @param string $pwd 密码（md5）
     * @param string $strOs 平台
     * @return string|boolean
     */
    public function getTokenByPwd($person, $pwd,$strOs = 'web')
    {
        $emp = Employee::find()->where([
            'person_id' => $person['person_id']
        ])->one();
        if ($emp && $emp->id_card) {
            $_pwd = substr($emp->id_card, - 6);
            if ($pwd == md5($_pwd)) {
                $token = md5($strOs.$person['person_id'].'##'.$pwd.'@'.time());
                $key = 'salary_access_token_'.$strOs.'_'.$person['person_id'];
                yii::$app->cache->set($key, $token,$this->_token_time);
                return $token;
	        }
	    }
	    return false;
	}
	
	/**
	 * 验证token
	 * @param string $token
	 * @param array $person 登入用户
	 * @param string $strOs 平台
	 */
	public function checkToken($token,$person,$strOs = 'web')
	{
	    if($token){
	        $key = 'salary_access_token_'.$strOs.'_'.$person['person_id'];
	        $_token = yii::$app->cache->get($key);
	        if($token === $_token){
	            return true;
	        }
	    }
	    return false;
	}
	
	/**
	 * 是否是人事
	 * @param array $arrPersonRole
	 */
	public function isHr($arrPersonRole)
	{
	    if(in_array('all_salary',$arrPersonRole['roleInfo'])){
	        return true;
	    }else{
	        return false;
	    }
	}
	
	
	/**
	 * 薪酬导入日志
	 * @param string $data 
	 * @param int $person_id 操作者id 
	 * @param string $person_name 操作者
	 */
	public function addLog($data='',$person_id=0,$person_name='')
	{
		$model = new SalaryLog();
		$model->data = is_array($data) ? json_encode($data,JSON_UNESCAPED_UNICODE) : $data;
		$model->create_date = date('Y-m-d H:i:s',time());
		$model->create_time = time();
		$model->person_name = $person_name;
		$model->person_id = $person_id;
		try{
			if(!$model->insert()){
			    throw new \Exception('error');
			}
		}catch (\Exception $e){
			yii::info("薪酬导入日志错误 {$person_name} {$model->data}");
		}catch (\Throwable $e){
			yii::info("薪酬导入日志错误 {$person_name} {$model->data}");
		}
	}
}