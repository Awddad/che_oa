<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/10
 * Time: 17:23
 */

namespace app\logic\server;


use app\logic\Logic;

/**
 * 第三方接口相关逻辑
 *
 * Class ThirdServer
 * @package app\logic\server
 */
class Server extends Logic
{
    public $data = '--- 请求开始 ---'.PHP_EOL;


    /**
     * GET 请求
     * @param $url
     */
    public function httpGet($url)
    {
        $this->data .= '请求链接：'.$url.PHP_EOL;
        return $this->httpSend($url);
    }

    /**
     * POST 请求
     * @param $url
     * @param $data
     * @return mixed
     */
    public function httpPost($url, $data)
    {
        $this->data .= '请求链接：'.$url.PHP_EOL;
        $this->data .= '请求参数：'.json_encode($data).PHP_EOL;
        return $this->httpSend($url, $data, 'POST');
    }

    /**
     * 发送HTTP 请求
     * @param $url
     * @param array $param
     * @param string $type
     * @return mixed
     */
    public function httpSend($url, $param = [] ,$type = 'GET')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if($type == 'POST'){
            $param = http_build_query($param);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        }
        $data = curl_exec($ch);//运行curl
        $this->data .= '请求结果:'.$data.PHP_EOL.'--- 请求结束 ---'.PHP_EOL;
        //增加日志
        Logs::instance()->addLogs($this->data);
        curl_close($ch);
        return json_decode($data, true);
    }
}