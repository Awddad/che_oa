<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'timeZone' => 'Asia/Shanghai',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
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
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'Ecb0jTYLy3LKDtkASW3CrmO6dukqB4I6',
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                if (\Yii::$app->controller->id != 'site')//预留登录后门用
                {
                    $response = $event->sender;
                    $response->data = [
                        'code' => isset($response->data['code']) ? $response->data['code'] : 0,
                        'message' => isset($response->data['message']) ? $response->data['message'] : '',
                        'data' => isset($response->data['data']) ? $response->data['data'] : NULL,
                    ];
                    $response->statusCode = 200;
                }
            },

        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'session'=>array(
            'timeout'=> 3600 * 12,
        ),
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
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
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1', '*'],
    ];
}

return $config;
