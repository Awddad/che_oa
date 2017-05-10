<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/9
 * Time: 15:40
 */

namespace app\modules\oa_v1\models;


use app\models\CaiWuFuKuan;
use yii\web\UploadedFile;

/**
 * 付款确认表单
 *
 * Class PayConfirmForm
 * @package app\modules\oa_v1\models
 */
class PayConfirmForm extends CaiWuFuKuan
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'apply_id', 'org_id', 'org_name', 'bank_card_id', 'fu_kuan_id', 'fu_kuan_time'
                ],
                'required'
            ],
            [['org_id', 'type', 'fu_kuan_time', 'create_time'], 'integer'],
            [['tips'], 'string'],
            [['apply_id'], 'string', 'max' => 20],
            [['org_name', 'bank_name', 'bank_name_des', 'fu_kuan_id', 'pics'], 'string', 'max' => 255],
            [['bank_card_id'], 'string', 'max' => 16],
        ];
    }


    /**
     * 保存文件
     *
     * @param $name
     * @return array |boolean
     */
    public function saveUploadFile($name)
    {
        $files = UploadedFile::getInstancesByName($name);
        if(empty($files)) {
            $this->addError('格式错误');
            return false;
        }
        $basePath = \Yii::$app->basePath.'/web';
        $filePath = '/upload/payconfirm/'.date('Y-m-d').'/';
        $rootPath = $basePath.$filePath;
        $data = [];
        foreach ($files as  $file) {
            $ext = $file->getExtension();
            if(!in_array($ext, ['jpg', 'gif', 'png'])) {
                $this->addError('格式错误');
                return false;
            }
            $randName = $file->name;
            if (!file_exists($rootPath)) {
                mkdir($rootPath, 0777, true);
            }
            $fileName = $rootPath.$randName;
            $file->saveAs($fileName);
            $data[] = 'http://'.$_SERVER['HTTP_HOST'].$filePath . $randName
            ;
        }
        return implode(",", $data);
    }
}