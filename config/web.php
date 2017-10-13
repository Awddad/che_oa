<?php
use yii\helpers\ArrayHelper;

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'timeZone' => 'Asia/Shanghai',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log','chelog'],
    'language' => 'zh-CN',
    'modules' => [
        'oa_v1' => [
            'class' => 'app\modules\oa_v1\module',
        ],
        'third_api' => [ //对外的第三方接口地址，如权限的通知接受等
            'class' => 'app\modules\third_api\module',
        ],
    ],
    'components' => [
        'request' => [
            // !!! insert a secroet key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'Ecb0jTYLy3LKDtkASW3CrmO6dukqB4I6',
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                if (\Yii::$app->controller->module->id == 'oa_v1')//预留登录后门用
                {
                    $response = $event->sender;
                    if ($response->data !== null) {
                        if ($response->isSuccessful) {
                            $response->data = [
                                'message' => ArrayHelper::getValue($response->data, 'message', ''),
                                'code' => intval(ArrayHelper::getValue($response->data, 'code', 0)),
                                'data' => ArrayHelper::getValue($response->data, 'data'),
                            ];
                        } else {
                            $code = intval(Yii::$app->errorHandler->exception->getCode());
                            $response->data = [
                                'message' => $code < 200 ? '系统异常！' : Yii::$app->errorHandler->exception->getMessage(),
                                'code' => $code ? :  $response->statusCode,
                                'data' => [],
                            ];
                        }
                        $response->statusCode = 200;
                    }
                }
            },

        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'session' => [
            'name' => 'project-oa',
            'cookieParams' => ['httponly' => true, 'lifetime' => 3600 * 4],
            'timeout'=> 3600 * 4,
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => require (__DIR__. '/mail.php'),
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logVars' => ['_GET', '_POST', '_FILES',]
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        'formatter' => [
            'datetimeFormat' => 'php:Y-m-d H:i',
            'currencyCode' => 'CNY',
        ],
        'chelog' => [//初始化车城日志插件
            'class' => 'app\che\Logs'
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1', '*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1', '*'],
    ];
}

return $config;
