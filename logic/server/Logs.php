<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/16
 * Time: 16:30
 */

namespace app\logic\server;


use app\logic\Logic;
use phpDocumentor\Reflection\Types\Null_;

/**
 * 写入日志
 * Class Logs
 * @package app\logic\server
 */
class Logs extends Logic
{
    /**
     * 增加日志
     *
     * @param $fileName
     * @param $string
     * @return true
     */
    public function addLogs($string, $fileName = NULL)
    {
        if (!$fileName) {
            $fileName = date('Ymd').'.log';
        }
        $filePath = $this->getPath().$fileName;
        $this->writeLogs($filePath, $string);
        return true;
    }

    /**
     * 获取目录
     *
     * @return string
     */
    public function getPath()
    {
        $path =  \Yii::$app->basePath  . '/runtime/logs/api/'.date('Ym').'/';
        if(!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        return $path;
    }

    /**
     * 写入log
     *
     * @param $filePath
     * @param $string
     * @return bool
     */
    public function writeLogs($filePath, $string)
    {
        if ($string) {
            $fp = @fopen($filePath, 'ab');
            if ($fp) {
                @flock($fp, LOCK_EX);
                fwrite($fp, $string . PHP_EOL);
                @flock($fp, LOCK_UN);
                @fclose($fp);
            } else {
                return false;
            }
        }
        return false;
    }
}