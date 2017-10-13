<?php
/**
 * 日志插件初始化，主要设置
 */
namespace app\che;

use Yii;
use yii\base\Application;
use yii\base\Object;

class Logs extends Object
{
    public function init()
    {
        //在App里面注册一个处理事件，该事件对应上面提到的yii\base\Application::run()里的第一个事件
        //将日志插件注册一下，主要是绑定错误捕捉和异常捕捉自定义的方法
        Yii::$app->on(Application::EVENT_BEFORE_REQUEST, function($event){
            \cheframework\logs\Log::instance()->register();
        });
    }
}


