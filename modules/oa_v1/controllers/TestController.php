<?php

namespace app\modules\oa_v1\controllers;

use yii;
use yii\base\Controller;

class TestController extends Controller
{
	public function actionIndex()
	{
		$myClass = new MyClass();
		$myBehavior = new MyBehavior();
		
		// Step 3: 将行为绑定到类上
		$myClass->attachBehavior('myBehavior', $myBehavior);
		
		// Step 4: 访问行为中的属性和方法，就和访问类自身的属性和方法一样
		echo $myClass->property1.'<br>';
		echo $myClass->method();
	}
}



// Step 1: 定义一个将绑定行为的类
class MyClass extends yii\base\Component
{
    public $a = '123';
    
    public function method()
    {
    	echo '4444444';
    }
}

// Step 2: 定义一个行为类，他将绑定到MyClass上
class MyBehavior extends yii\base\Behavior
{
    // 行为的一个属性
    public $property1 = 'This is property in MyBehavior.';
    
    //public $a = '444';

    // 行为的一个方法
    public function method1()
    {
        return 'Method in MyBehavior is called.';
    }
    
    public function method()
    {
    	var_dump($this->owner->a);die();
    }
}

