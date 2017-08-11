<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/8/11
 * Time: 10:29
 */
return [
    'class' => yii\swiftmailer\Mailer::className(),
    'useFileTransport' => false,
    'transport' => [
        'class' => 'Swift_SmtpTransport',
        'host' => 'smtp.exmail.qq.com',
        'username' => 'oa@che.com',
        'password' => 'kmmaVd3hTuu5gMTR',
        'port' => '465',
        'encryption' => 'ssl',
    ],
    //发送的邮件信息配置
    'messageConfig' => [
        'charset' => 'utf-8',
        'from' => ['oa@che.com' => 'OA系统通知']
    ],
];