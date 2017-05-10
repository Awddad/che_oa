<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/9
 * Time: 15:41
 */

namespace app\modules\oa_v1\models;


use app\models\CaiWuShouKuan;
use yii\web\UploadedFile;


/**
 * 还款确认
 *
 * Class BackConfirmForm
 * @package app\modules\oa_v1\models
 */
class BackConfirmForm extends CaiWuShouKuan
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'apply_id', 'org_id', 'org_name', 'bank_card_id',
                    'bank_name', 'bank_name_des', 'shou_kuan_id', 'shou_kuan_time',
                    'create_cai_wu_log'
                ],
                'required'
            ],
            [['org_id', 'type', 'shou_kuan_time', 'create_cai_wu_log'], 'integer'],
            [['tips'], 'string'],
            [['apply_id', 'org_name', 'bank_name', 'bank_name_des', 'shou_kuan_id', 'pics'], 'string', 'max' => 255],
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
        $filePath = '/upload/payback/'.date('Y-m-d').'/';
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