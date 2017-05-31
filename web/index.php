<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
if(is_file(__DIR__ . '/dev.txt'))//开发环境 在网站web根目录放一个dev.txt文件
{
    defined('YII_ENV') or define('YII_ENV', 'dev');
}
else if(is_file(__DIR__ . '/test.txt'))//测试环境 在网站web根目录放一个test.txt文件
{
    defined('YII_ENV') or define('YII_ENV', 'test');
}
else//不是测试环境也不是开发环境的话  默认是正式环境
{
    defined('YII_ENV') or define('YII_ENV', 'prod');
}

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/web.php');

(new yii\web\Application($config))->run();
