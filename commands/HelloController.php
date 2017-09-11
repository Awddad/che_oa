<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\Apply;
use app\models\ApprovalLog;
use yii\console\Controller;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";
    }
    
    public function actionSql()
    {
        $sql = <<<_SQL
_SQL;
        \Yii::$app->db->createCommand($sql)->execute();

    }
    
    /**
     * 更新结束时间
     */
    public function actionFixed()
    {
        $data = \app\models\Apply::find()->where(['status' => 99])->all();
        /**
         * @var Apply $v
         */
        foreach ($data as $k => $v) {
            $approvalLog = ApprovalLog::find()->where([
                'apply_id' => $v->apply_id
            ])->orderBy('approval_time desc')->limit(1)->one();
            $v->end_time = $approvalLog->approval_time;
            $v->save();
            echo $v->apply_id.PHP_EOL;
        }
    }

    public function actionConfig()
    {
        $data = \app\models\ApprovalConfig::find()->all();
        foreach($data as $k=>$v){
            $config = json_decode($v->approval);
            if(!$config){
                continue;
            }
            $approval = [];
            foreach($config as $kk => $vv){
                foreach($vv as $vvv) {
                    $approval[$kk][] = [
                        'type' => 1,
                        'value' => $vvv,
                    ];
                }
            }
            $v->approval = json_encode($approval);
            if(!$v->save()){
                echo current($v->getFirstErrors()).PHP_EOL;
            }

        }
    }
}
