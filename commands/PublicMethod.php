<?php
namespace app\commands;
/**
 * @功能：公共方法存放位置
 * @作者：王雕
 * @创建时间：2017-05-04
 */
class PublicMethod
{
    /**
     * @功能：curl请求发起函数
     * @作者：王雕
     * @创建时间：2017-05-05
     * @param string $url       请求的url
     * @param string $options   curl参数设置
     * @return string $result   curl请求结果
     */
    public static function curl_get_contents($url, $options = array())
    {
        $default = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/17.0 Firefox/17.0",
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 3,
        );
        foreach ($options as $key => $value)
        {
            $default[$key] = $value;
        }
        $ch = curl_init();
        curl_setopt_array($ch, $default);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    
    /**
     * @功能：curl请求发起函数 - get 请求
     * @作者：王雕
     * @创建时间：2017-05-05
     * @param string $url       url地址
     * @param array $params     get参数
     * @param array $options    curl配置参数
     * @return string           curl请求结果
     */
    public static function http_get($url, $params = array(), $options = array())
    {
        $paramsFMT = array();
        foreach ($params as $key => $val)
        {
            $paramsFMT[] = $key . "=" . urlencode($val);
        }
        return self::curl_get_contents($url . ($paramsFMT ? ( "?" . join("&", $paramsFMT)) : ""), $options);
    }

    /**
     * @功能：curl请求发起函数 - post 请求
     * @作者：王雕
     * @创建时间：2017-05-05
     * @param string $url       url地址
     * @param array $params     post参数
     * @param array $options    curl配置参数
     * @return string           curl请求结果
     */
    public static function http_post($url, $params = array(), $options = array())
    {
        $paramsFMT = array();
        foreach ($params as $key => $val)
        {
            $paramsFMT[] = $key . "=" . urlencode($val);
        }
        $options[CURLOPT_POST] = 1;
        $options[CURLOPT_POSTFIELDS] = join("&", $paramsFMT);
        return self::curl_get_contents($url, $options);
    }    

}