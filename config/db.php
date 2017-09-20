<?php
if(YII_ENV_DEV) //开发库
{
    return [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=192.168.1.22;dbname=che_oa',
        'username' => 'oa',
        'password' => 'che@oa',
        'charset' => 'utf8',
    ];    
}
else if(YII_ENV_TEST) //测试环境
{
    return [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=192.168.1.21;dbname=che_oa',
        'username' => 'oa',
        'password' => 'che@oa',
        'charset' => 'utf8',
    ];
}
else //正式环境
{
    return [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=rm-uf6o9375r0p06pfcu.mysql.rds.aliyuncs.com;dbname=oa',
        'username' => 'oa',
        'password' => 'Oa@che!@3',
        'charset' => 'utf8',
    ];
}
