<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/9
 * Time: 17:18
 */

namespace app\modules\oa_v1\logic;

use Jasny\SSO\Broker;
use Yii;
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
    public $apply_model = [
        '1' => '\app\models\Baoxiao',
        '2' => '\app\models\JieKuan',
        '3' => '\app\models\PayBack',
        '4' => '\app\models\ApplyPay',
        '5' => '\app\models\ApplyBuy',
        '6' => '\app\models\ApplyDemand',
        '7' => '\app\models\ApplyUseChapter',
        '8' => '\app\models\AssetGet',
        '9' => '\app\models\AssetBack',
        '10' => '\app\models\ApplyPositive',
        '11' => '\app\models\ApplyLeave',
        '12' => '\app\models\ApplyTransfer',
        '13' => '\app\models\ApplyOpen',
        '14' => '\app\models\GoodsUp',
    ];
    
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
        if(!isset($data['tips_title']) || !isset($data['tips_content']) || !isset($data['receivers'])) {
            $this->error = '参数错误';
            return false;
        }
        if($toAll){
            $data['to_all'] = 1;
        }
        $params = \Yii::$app->params['quan_xian'];
        $data['_token'] = $params['auth_token'];
        $data['window_title'] = 'OA系统信息提醒';
        $rst = Server::instance()->httpPost($params['auth_api_url'].'/bqq/tips', $data);
        return $rst;
        
    }
    
    /**
     *
     * @param $error
     *
     * @return mixed
     */
    public function getFirstError($error)
    {
        if(empty($error)){
            return false;
        }
        $firstErr = current($error);
        
        return current($firstErr);
    }
    
    
    /**
     * @return Broker
     */
    public function ssoClient()
    {
        $serverUrl = Yii::$app->params['quan_xian']['auth_sso_url'];//单点登录地址
        $brokerId = Yii::$app->params['quan_xian']['auth_broker_id'];//项目appID
        $brokerSecret = Yii::$app->params['quan_xian']['auth_broker_secret'];//配置的项目 Secret
        $broker = new Broker($serverUrl, $brokerId, $brokerSecret);
        return $broker;
    }
    
    /**
     * @param $error
     *
     * @return string
     */
    public function getErrorMessage($error)
    {
        $message = '';
        foreach ($error as $v){
            $message .= implode('', $v).' ';
        }
        return $message;
    }
}