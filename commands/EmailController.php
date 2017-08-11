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
            if($person->email) {
                $email = \Yii::$app->mailer->compose()->setTo(
                    $person->email
                )->setSubject('审批单提醒！')->setHtmlBody(
                    '今天你还有' . $v['total'] . '个审批未处理，快去处理吧。<a href="http://oa.admin.che.com/oa/index.html#/approvals/index?ispage=1">点击处理</a>'
                )->send();
                if (!$email) {
                    echo '发送失败'.PHP_EOL;
                } else {
                    echo '发送成功'.PHP_EOL;
                }
            }
        }
    }
}