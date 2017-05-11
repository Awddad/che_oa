<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/10
 * Time: 17:24
 */

namespace app\logic\server;


/**
 * 财务系统
 *
 * Class FinanceServer
 * @package app\logic\server
 */
class FinanceServer extends ThirdServer
{
    /**
     * 请求TOKEN
     *
     * @var string
     */
    public $token = 'debf6cc22a8baf00904acc5f42535575';

    /**
     * 基础Url
     * @var string
     */
    protected $baseUrl = 'http://test.pocket.checheng.net/api/';

    /**
     * 获取科目标签树形结构 URL
     * @var string
     */
    public $tagTreeUrl = 'tag-tree';

    /**
     * 获取科目标签树形结构
     *
     * @return bool|mixed
     */
    public function getTagTree()
    {
        return $this->httpPost($this->baseUrl.$this->tagTreeUrl, ['_token' => $this->token]);
    }

    public function saveTreeTag()
    {

    }
}