<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/18
 * Time: 20:39
 */

namespace app\modules\oa_v1\controllers;


use app\modules\oa_v1\models\FileForm;

/**
 * 文件上传
 *
 *
 * Class UploadController
 * @package app\modules\oa_v1\controllers
 */
class UploadController extends BaseController
{
    public function verbs()
    {
        return [
            'file' => ['post','OPTIONS']
        ];
    }

    public function actionImage()
    {
        $model = new FileForm();
        $img = $model->saveUploadImg();
        if(!$img) {
            return $this->_return($img, 1011);
        }
        return $this->_return($img);
    }

    public function actionFile()
    {
        $model = new FileForm();
        $file = $model->saveUploadFile();
        if(!$file) {
            return $this->_return($file, 1012);
        }
        return $this->_return($file);
    }
}