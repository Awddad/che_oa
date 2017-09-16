<?php
namespace app\logic\server;

use Yii;

class PhoneLetter extends Server
{
    /**
     * 短信相关的配置
     */
    private static $url = 'http://139.196.250.134:8090/smsApi!mt';//接口地址
    private static $user = '17798999998';//接口账号
    private static $appKey = '14f6bf67387c417087898d2a106c120d';//接口token
    private static $extCode = '362928';//扩展码
    
    /**
     * 语音短信相关的配置
     */
    private static $yy_url = 'http://139.224.34.60:8099/api/playSoundMsg';
    private static $yy_appId = '4e1dfdf2a9c2459e9c5d0b3e8c0b85ef';
    private static $yy_apptoken = 'e6cd6def0fcf4c71942c810aff1561ef';

    /**
     * 发送的类型
     */
    const SENT_TYPE_TEXT = 1;           // 文字短信
    const SENT_TYPE_APP = 2;            // APP短信
    const SENT_TYPE_VOICE = 3;          // 语音短信


    /**
     * @功能：短信
     * @param string $strPhone 接收人手机号
     * @param string $strContent 内容
     * @return bool
     */
    public function sendSms($strPhone, $strContent)
    {
        $arrPost = [
            'userAccount' => self::$user,
            'appKey' => self::$appKey,
            'extCode' => self::$extCode,
            'cpmId' => date('YmdHis') . rand(10000,99999),
            'mobile' => $strPhone,
            'message' => $strContent
        ];
        $arrRes = $this->httpPost(self::$url, $arrPost);
        return ($arrRes['respCode'] == 200 ? true : false);
    }


    /**
     * @功能：语音短信
     * @param string $strPhone 接收人手机号
     * @param string $strContent 语音播报内容
     * @return bool
     */
    public function sendYY($strPhone, $strContent)
    {
        $arrPost = [
            'appId' => self::$yy_appId,
            'callee' => '86' . $strPhone,
            'playtimes' => 2,//播报两次
            'attemptInterval' => 2,//每次间隔2秒
            'msg' => $strContent,
            'ti' => time()
        ]; 
        $arrPost['au'] = strtoupper(md5(self::$yy_appId . self::$yy_apptoken . $arrPost['ti']));
        $arrRes = $this->httpPost(self::$yy_url, $arrPost);
        return ($arrRes[0]['resultCode'] == 0 ? true : false);
    }

}
