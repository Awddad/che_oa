<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/8/11
 * Time: 16:53
 */

namespace app\modules\oa_v1\logic;


use app\logic\Logic;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * 商品上架逻辑代码
 *
 * Class GoodsUpLogic
 * @package app\modules\oa_v1\logic
 */
class GoodsUpLogic extends Logic
{
    /**
     * 电商接口HOST
     * @var
     */
    public $host;
    
    /**
     * 品牌
     * @var string
     */
    public $brandUrl = '/che/oa/brands';
    
    /**
     * 厂商
     * @var string
     */
    public $factoryUrl = '/che/oa/factory';
    
    /**
     * 车系
     * @var string
     */
    public $seriesUrl = '/che/oa/series';
    
    /**
     * 车型
     * @var string
     */
    public $carUrl = '/che/oa/cars';
    
    /**
     * 颜色
     * @var string
     */
    public $colorUrl = '/che/oa/colors';
    
    /**
     * @var Client
     */
    public $httpClient;
    
    /**
     * 初始化
     */
    public function init()
    {
        $this->host = \Yii::$app->params['shop']['host'];
        $this->httpClient = new Client();
    }
    
    /**
     * 品牌
     *
     * @return bool
     */
    public function brand()
    {
        $uri = $this->host.$this->brandUrl;
        $response =  $this->httpClient->get($uri);
        return  $this->getData($response);
    }
    
    /**
     * 厂商
     * @param $brandId
     *
     * @return bool | array
     */
    public function factory($brandId)
    {
        $uri = $this->host.$this->factoryUrl;
        $response =  $this->httpClient->get($uri, [
            'query' => ['brandId' => $brandId]
        ]);
        return  $this->getData($response);
    }
    
    /**
     * 车系
     *
     * @param $brandId
     * @param $factoryId
     *
     * @return bool
     */
    public function series($brandId, $factoryId)
    {
        $uri = $this->host.$this->seriesUrl;
        $response =  $this->httpClient->get($uri, [
            'query' => [
                'brandId' => $brandId,
                'factoryId' => $factoryId,
            ]
        ]);
        return  $this->getData($response);
    }
    
    /**
     * 车型
     *
     * @param $seriesId
     *
     * @return bool
     */
    public function car($seriesId)
    {
        $uri = $this->host.$this->carUrl;
        $response =  $this->httpClient->get($uri, [
            'query' => [
                'seriesId' => $seriesId,
            ]
        ]);
        return  $this->getData($response);
    }
    
    /**
     * 颜色
     *
     * @param $carId
     *
     * @return bool
     */
    public function colors($carId)
    {
        $uri = $this->host.$this->colorUrl;
        $response =  $this->httpClient->get($uri, [
            'query' => [
                'carId' => $carId,
            ]
        ]);
        return  $this->getData($response);
    }
    
    /**
     * 获取结果
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    private function getData($response)
    {
        $data = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        if ($response->getStatusCode() == 200 && !empty($data) && $data['code'] == 200) {
            return $data['detail'];
        }
        return false;
    }
}