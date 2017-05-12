<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/10
 * Time: 17:23
 */

namespace app\logic\server;



/**
 * 第三方接口相关逻辑
 *
 * Class ThirdServer
 * @package app\logic\server
 */
class ThirdServer extends Server
{
    /**
     * 请求TOKEN
     *
     * @var string
     */
    public $token = 'debf6cc22a8baf00904acc5f42535575';

    /**
     * 基础URL
     * @var string
     */
    protected $baseUrl = 'http://test.pocket.checheng.net/api/';

    /**
     * 获取科目标签树形结构 URL
     * @var string
     */
    public $tagTreeUrl = 'tag-tree';

    /**
     * 获取组织架构下的可用卡号(包含全局卡号)
     *
     * @var string
     */
    public $accountUrl = 'accounts';

    /**
     * 生成订单流水
     *
     * @var string
     */
    public $paymentUrl = 'payment';


    /**
     * 获取科目标签树形结构
     *
     * @return bool|mixed
     */
    public function getTagTree()
    {
        return $this->httpPost($this->baseUrl.$this->tagTreeUrl, ['_token' => $this->token]);
    }

    /**
     * 获取科目标签树形结构
     *
     * @param $organizationId
     * @return mixed
     */
    public function getAccount($organizationId)
    {
        $rst = $this->httpPost($this->baseUrl.$this->accountUrl, [
            '_token' => $this->token,
            'organization_id' => $organizationId
        ]);
        if($rst['success'] == 1) {
            return $rst['data'];
        }
        return false;
    }


    /**
     * 创建流水
     *
     * @param $param
     * @return bool
     */
    public function payment($param)
    {
        $param['_token'] = $this->token;
        return $this->httpPost($this->baseUrl.$this->paymentUrl, $param);
    }
}