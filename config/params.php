<?php
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
        ]
    ];
}
else
{
//    return [
//        'adminEmail' => 'admin@example.com',
//        'quan_xian' => [
//            'auth_sso_url' => 'http://auth.admin.che.com',//单点登录地址
//            'auth_broker_id' => '1564438672652512',//项目appID
//            'auth_broker_secret' => '4a5fc71ce117d47c998a8179c14f3c17',//配置的项目 Secret
//            'auth_sso_login_url' => 'http://admin.che.com/login.php',//跳转的单点登录页面
//            'auth_api_url' => 'http://sso.checheng.net/api',//拉取组织和人员以及角色信息的接口地址
//            'auth_token' => '61d08b77da5e0fef8e433c608b059820',//token
//        ],
//        'cai_wu' => [
//            'token' => 'debf6cc22a8baf00904acc5f42535575',
//            'baseUrl' => 'http://test.pocket.checheng.net/api/'
//        ]
//    ];
}
