<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

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
ALTER TABLE `che_oa`.`oa_apply_use_chapter` ADD COLUMN `use_type` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '1:用章 2:借章' AFTER `files`;
ALTER TABLE `che_oa`.`oa_apply_use_chapter` ADD COLUMN `name_path` varchar(128) DEFAULT '' COMMENT '公司路径（重新申请勇）' AFTER `use_type`;
ALTER TABLE `oa_talent`
ADD COLUMN `face_time`  varchar(20) NOT NULL DEFAULT '' COMMENT '面试时间' AFTER `disagree_reason`;
ADD COLUMN `need_test`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否需要考试 0不需要 1需要' AFTER `face_time`,
ADD COLUMN `choice_score`  tinyint(3) NOT NULL DEFAULT -1 COMMENT '选择题分数' AFTER `need_test`,
ADD COLUMN `answer_score`  tinyint(3) NOT NULL DEFAULT -1 COMMENT '问答题分数' AFTER `choice_score`;
_SQL;
        \Yii::$app->db->createCommand($sql)->execute();

    }
}
