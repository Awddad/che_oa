<?php
//项目基本信息的定义 - 暂时未找到合适的位置，先放在这里
defined('PROD_CODE') or define('PROD_CODE', '200');//产品名称
defined('PROD_NAME') or define('PROD_NAME', 'OA');//产品名称
defined('APP_NAME') or define('APP_NAME', 'OA系统');//项目名称
defined('APP_ID') or define('APP_ID', '200');//项目编号
defined('APP_VERSION') or define('APP_VERSION', '1.2.7');//版本号
if(!defined('LOG_DIR'))
{
    if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
        define('LOG_DIR', 'D:\log-test\oa');//日志插件记录的日志存放处
    } else {
        define('LOG_DIR', '/data/logs/oa/');//日志插件记录的日志存放处
    }
}
if(YII_ENV_DEV) //开发库
{
    return [
        'adminEmail' => 'admin@example.com',
        'quan_xian' => [
            'auth_sso_url' => 'http://test.sso-server.checheng.net',//单点登录地址
            'auth_broker_id' => '1566533804163555',//项目appID
            'auth_broker_secret' => 'b507cf99e021565e7b9f4772d10cbd77',//配置的项目 Secret
            'auth_sso_login_url' => 'http://test.sso.checheng.net',//跳转的单点登录页面
            'auth_api_url' => 'http://test.qx-api.checheng.net/api',//拉取组织和人员以及角色信息的接口地址
            'auth_token' => 'c455ad536e009c1832a4eb219d8fe5fb',//token

        ],
        'cai_wu' => [
            'token' => 'debf6cc22a8baf00904acc5f42535575',
            'baseUrl' => 'http://test.pocket.checheng.net/api/'
        ],
        'shop' => [
            'host' => 'http://dev.api.che.com'
        ]
    ];
}
else if(YII_ENV_TEST) //测试环境
{
    return [
        'adminEmail' => 'admin@example.com',
        'quan_xian' => [
            'auth_sso_url' => 'http://test.sso-server.checheng.net',//单点登录地址
            'auth_broker_id' => '1566533804163555',//项目appID
            'auth_broker_secret' => 'b507cf99e021565e7b9f4772d10cbd77',//配置的项目 Secret
            'auth_sso_login_url' => 'http://test.sso.checheng.net',//跳转的单点登录页面
            'auth_api_url' => 'http://test.qx-api.checheng.net/api',//拉取组织和人员以及角色信息的接口地址
            'auth_token' => 'c455ad536e009c1832a4eb219d8fe5fb',//token

        ],
        'cai_wu' => [
            'token' => 'debf6cc22a8baf00904acc5f42535575',
            'baseUrl' => 'http://test.pocket.checheng.net/api/'
        ],
        'shop' => [
            'host' => 'http://dev.api.che.com'
        ]
    ];
}
else
{
    return [
        'adminEmail' => 'admin@example.com',
        'quan_xian' => [
            'auth_sso_url' => 'http://auth.admin.che.com',//单点登录地址
            'auth_broker_id' => '1568902738126210',//项目appID
            'auth_broker_secret' => '8891c222969fe0f3b210ca3690d24a19',//配置的项目 Secret
            'auth_sso_login_url' => 'http://admin.che.com/login.php',//跳转的单点登录页面
            'auth_api_url' => 'http://sso.checheng.net/api',//拉取组织和人员以及角色信息的接口地址
            'auth_token' => '52afd3834c3f88f83e84254d9b8a26cc',//token
        ],
        'cai_wu' => [
            'token' => '1f320d157d95ca8f5cab2dd208ad8202',
            'baseUrl' => 'http://cw.admin.che.com/api/'
        ],
        'shop' => [
            'host' => 'http://api.che.com'
        ]
    ];
}
