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
    public function actionImage()
    {
        $model = new FileForm();
        return $this->_return($model->saveUploadImg());
    }

    public function actionFile()
    {
        $model = new FileForm();
        return $this->_return($model->saveUploadFile());
    }
}