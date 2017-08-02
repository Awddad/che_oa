<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/8/2
 * Time: 10:41
 */

namespace app\commands;


use app\models\ApprovalLog;
use app\models\Person;
use yii\console\Controller;

/**
 * 邮件发送
 * 
 * Class EmailController
 * @package app\commands
 */
class EmailController extends Controller
{
    public function actionIndex()
    {
        $data = ApprovalLog::find()->alias('a')->select([
            'a.approval_person_id',
            'count(*) as total'
        ])->rightJoin('oa_apply b', 'a.apply_id = b.apply_id')->where([
            'a.is_to_me_now' => 1,
            'a.result' => 0
        ])->groupBy('a.approval_person_id')->asArray()->all();
        foreach ($data as $v) {
            $person = Person::findOne($v['approval_person_id']);
            $rst =\Yii::$app->mailer->compose()
                ->setFrom(['xj@youmeng.com' => 'OA系统通知'])
                ->setTo('309264534@qq.com')
                ->setSubject('审批单提醒！')
                ->setHtmlBody('您有'.$v['total'] . '条审批单需要处理审批，请及时登陆OA系统处理！');
            if (!$rst->send()) {
                echo '发送失败';die;
            }
            echo '发送成功';
            die;
            
        }
        print_r($data);
    }
}