<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/7/26
 * Time: 11:17
 */

namespace app\logic\server;


class JobServer extends ThirdServer
{
    /**
     * 添加职位
     */
    const JOB_CREATE_URL = '/positions';
    
    /**
     * 获取职位列表
     */
    const JOB_ALL_URL = '/organizations/positions';
    
    /**
     * 新建职位
     *
     * @param $postData
     *
     * @return bool
     */
    public function create($postData)
    {
        $postData['_token'] = $this->token;
        $rst = $this->httpPost($this->baseUrl.self::JOB_CREATE_URL, $postData);
        if (!empty($rst) && $rst['code'] == 1 && $rst['data']) {
            return $rst['data'];
        }
        return false;
    }
    
    /**
     * 更新职位
     *
     * @param int $jobId
     * @param array $postData
     *
     * @return bool
     */
    public function update($jobId, $postData)
    {
        $url = $this->baseUrl.self::JOB_CREATE_URL.'/'.$jobId.'/update';
        $postData['_token'] = $this->token;
        $rst = $this->httpPost($url, $postData);
        if (!empty($rst) && $rst['code'] == 1) {
            return true;
        }
        return false;
    }
    
    /**
     * 删除职位
     *
     * @param $jobId
     *
     * @return bool
     */
    public function delete($jobId)
    {
        $url = $this->baseUrl.self::JOB_CREATE_URL.'/'.$jobId.'/delete';
        $rst = $this->httpPost($url, ['_token' => $this->token]);
        if (!empty($rst) && $rst['code'] == 1) {
            return true;
        }
        return false;
    }
    
    /**
     * 获取所有职位
     *
     * @return bool
     */
    public function getAllJob()
    {
        $url = $this->baseUrl.self::JOB_ALL_URL;
        $rst = $this->httpPost($url, ['_token' => $this->token]);
        if (!empty($rst) && $rst['code'] == 1) {
            return true;
        }
        return false;
    }
}