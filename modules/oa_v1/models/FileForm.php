<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/18
 * Time: 20:41
 */

namespace app\modules\oa_v1\models;


use yii\base\Model;
use yii\web\UploadedFile;

class FileForm extends Model
{

    /**
     * 文件路径
     * @var string
     */
    static $FILES_PATH = '/upload/files/';

    /**
     * 图片路径
     * @var string
     */
    static $PICS_PATH = '/upload/images/';


    public $files = [];

    public function rules()
    {
        return [
            ['files', 'required'],
        ];
    }

    public function save($type = 'pics')
    {
        if($type == 'pics') {
            return $this->saveUploadImg();
        } else {
            return $this->saveUploadFile();
        }
    }


    /**
     * 保存文件
     *
     * @param $name
     * @return array |boolean
     */
    public function saveUploadFile($name = 'files')
    {
        $files = UploadedFile::getInstancesByName($name);
//        if(empty($files)) {
//            return false;
//        }
        $basePath = \Yii::$app->basePath.'/web';

        $filePath = static::$FILES_PATH.date('Y-m-d').'/';

        $rootPath = $basePath.$filePath;
        $data = [];
        foreach ($files as  $file) {
            $ext = $file->getExtension();
            if (!in_array($ext, ['doc','xlsx','pdf', 'xls'])) {
                $this->addError($name, '格式错误');
                return false;
            }

            $randName = $file->name;
            if (!file_exists($rootPath)) {
                mkdir($rootPath, 0777, true);
            }
            $fileName = $rootPath.$randName;
            $file->saveAs($fileName);
            //$baseUrl = 'http://'.$_SERVER['HTTP_HOST'];
            $data[] = [
                'name' => $str = str_replace('.'.$ext, '', $file->name),
                'ext' => $ext,
                'url' => $filePath . $randName
            ];
        }
        return json_encode($data);
    }

    /**
     * 保存文件
     *
     * @param string $name
     * @return bool|string
     */
    public function saveUploadImg($name = 'pics')
    {
        $files = UploadedFile::getInstancesByName($name);
        if(empty($files)) {
            //$this->addError($name, '格式错误');
            return false;
        }
        $basePath = \Yii::$app->basePath.'/web';

        $filePath = static::$PICS_PATH.date('Y-m-d').'/';

        $rootPath = $basePath.$filePath;
        $data = [];
        foreach ($files as  $file) {
            $ext = $file->getExtension();
            if (!in_array($ext, ['jpg', 'gif', 'png'])) {
                $this->addError($name, '格式错误');
                return false;
            }
            $randName = $file->name;
            if (!file_exists($rootPath)) {
                mkdir($rootPath, 0777, true);
            }
            $fileName = $rootPath.$randName;
            $file->saveAs($fileName);
            //$baseUrl = 'http://'.$_SERVER['HTTP_HOST'];
            $data[] = $filePath . $randName;
        }
        return implode(',', $data);
    }
}