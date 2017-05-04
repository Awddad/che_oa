<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/4
 * Time: 9:45
 */

namespace app\modules\oa_v1\controllers;


use yii\filters\auth\CompositeAuth;
use yii\filters\ContentNegotiator;
use yii\filters\RateLimiter;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\web\Response;

/**
 * 接口基础
 *
 * Class BaseController
 * @package app\modules\oa_v1\controllers
 */
class BaseController extends Controller
{
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'verbFilter' => [
                'class' => VerbFilter::className(),
                'actions' => $this->verbs(),
            ],
            'authenticator' => [
                'class' => CompositeAuth::className(),
            ],
            'rateLimiter' => [
                'class' => RateLimiter::className(),
            ],
        ];
    }

    /**
     * 统一返回格式
     *
     * @param string|array|object $data 返回内容
     * @param string $message
     * @param int $code
     * @return array
     */
    public function _return($data, $message = 'success', $code = 200)
    {
        return compact('data', 'message', $code);
    }
}