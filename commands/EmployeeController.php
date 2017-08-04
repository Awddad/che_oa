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
        $success = 0;
        $fail = 0;
        $query = Apply::find()
        ->select('a.person_id')
        ->alias('a')
        ->join('LEFT JOIN', ApplyPositive::tableName().' p', 'a.apply_id = p.apply_id')
        ->where(['a.type' => 10, 'status' => 99]);
        
        //echo $query->createCommand()->getRawSql();die();
        $res = $query->asArray()->all();
        if($res){
            $shiyong_id = EmployeeType::findOne(['slug' => 'shiyong'])->id;
            $zhengshi_id = EmployeeType::findOne(['slug' => 'zhengshi'])->id;
            foreach($res as $v){
                $employee = Employee::findOne(['person_id' => $v['person_id'], 'employee_type' => $shiyong_id]);
                if(empty($employee)){
                    continue;
                }
                $employee->employee_type = $zhengshi_id;
                if($employee->save()){
                    $success += 1;
                }else{
                    $fail += 1;        
                }
            }
        }
        echo "success count:{$success}".PHP_EOL."fail count:{$fail}";
    }
}