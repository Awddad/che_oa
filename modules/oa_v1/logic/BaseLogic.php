<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/9
 * Time: 17:18
 */

namespace app\modules\oa_v1\logic;


use app\logic\Logic;
use app\logic\server\Server;
use yii\data\Pagination;

/**
 * 逻辑基础方法
 *
 * Class BaseLogic
 * @package app\modules\oa_v1\logic
 */
class BaseLogic extends Logic
{
    /**
     * 分页数据处理
     *
     * @param Pagination $pagination
     * @return array
     */
    public function pageFix($pagination)
    {
        return [
            'totalCount' => intval($pagination->totalCount),
            'pageCount' => intval($pagination->getPageCount()),
            'currentPage' => intval($pagination->getPage() + 1),
            'perPage' => intval($pagination->getPageSize()),
        ];
    }
    
    /**
     * 发送企业QQ广播通知
     * @param $data
     * @param $toAll
     * @return bool
     */
    public function sendQqMsg($data, $toAll = 0)
    {
        if($toAll){
            $data['to_all'] = 1;
        }
        $params = \Yii::$app->params['quan_xian'];
        $data['_token'] = $params['auth_token'];
        $rst = Server::instance()->httpPost($params['auth_api_url'].'/bqq/tips', $data);
        return $rst;
        
    }
}