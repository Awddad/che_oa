<?php
namespace app\commands;

use yii\console\Controller;
use app\models\Apply;
use app\models\ApplyPositive;
use app\models\Employee;
use app\models\EmployeeType;

/**
 * Class EmployeeController
 * @package app\commands
 */
class EmployeeController extends Controller
{
    public function actionPositive()
    {
        $shiyong_id = EmployeeType::findOne(['slug' => 'shiyong'])->id;
        $zhengshi_id = EmployeeType::findOne(['slug' => 'zhengshi'])->id;
        $success = 0;
        $query = Apply::find()
        ->select('a.person_id,positive_time')
        ->alias('a')
        ->join('LEFT JOIN', ApplyPositive::tableName().' p', 'a.apply_id = p.apply_id')
        ->join('LEFT JOIN',Employee::tableName().' e','e.person_id = a.person_id')
        ->where(['a.type' => 10, 'a.status' => 99,'e.employee_type'=>$shiyong_id]);
        
        //echo $query->createCommand()->getRawSql();die();
        $res = $query->asArray()->all();
        if($res){
            $person_ids = [];
            foreach($res as $v){
                if($v['positive_time'] && (strtotime($v['positive_time']) <= time())){
                    $person_ids[] = $v['person_id'];
                }
            }
            if(count($person_ids) > 0){
                $success = Employee::updateAll(['employee_type'=>$zhengshi_id],['in','person_id',$person_ids]);
            }
        }
        echo "success count:{$success}";
    }
}