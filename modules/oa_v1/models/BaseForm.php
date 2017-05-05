<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/5
 * Time: 11:21
 */

namespace app\modules\oa_v1\models;


use app\modules\oa_v1\logic\PersonLogic;
use app\models\Apply;
use app\models\Person;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * 申请基础表单
 *
 *
 * Class BaseForm
 * @package app\modules\oa_v1\models
 */
class BaseForm extends Model
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


    /**
     * 审批人
     * @var array
     */
    public $approval_persons = [];

    /**
     * 抄送人
     * @var array
     */
    public $copy_person = [];


    /**
     * 审批人
     *
     * @param Apply $apply
     */
    public function approvalPerson($apply)
    {
        $data = [];
        $i = 1;
        $count = count($this->approval_persons);
        foreach ($this->approval_persons as $v) {
            $personName = PersonLogic::instance()->getPersonName($v['copy_person_id']);
            $end = $i == $count ? 1 : 0;
            $begin = $i == 1 ? : 0;
            $data[] = [
                $apply->apply_id,
                $personName,
                $v['copy_person_id'],
                $i,
                $end,
                $begin
            ];
            $i++;
        }
        \Yii::$app->db->createCommand()->batchInsert('oa_approval_log',[
            'apply_id', 'approval_person', 'approval_person_id', 'steep', 'is_end','is_to_me_now'
        ],$data);
    }

    /**
     * 抄送人
     *
     * @param Apply $apply
     */
    public function copyPerson($apply)
    {
        $data = [];
        foreach ($this->copy_person as $v) {
            $personName = PersonLogic::instance()->getPersonName($v['copy_person_id']);
            $data[] = [
                $apply->apply_id,
                $v['copy_person_id'],
                $personName,
            ];
        }
        \Yii::$app->db->createCommand()->batchInsert('oa_approval_log',[
            'apply_id', 'copy_person_id', 'copy_person',
        ],$data);
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
            $this->addError($name, '格式错误');
            return false;
        }
        $basePath = \Yii::$app->basePath.'/web';
        if($name == 'pics') {
            $filePath = static::$PICS_PATH.date('Y-m-d').'/';
        } else {
            $filePath = static::$FILES_PATH.date('Y-m-d').'/';
        }
        $rootPath = $basePath.$filePath;
        $data = [];
        foreach ($files as  $file) {
            $ext = $file->getExtension();
            if(!in_array($ext, ['jpg', 'gif', 'png'])) {
                $this->addError($name, '格式错误');
                return false;
            }
            $randName = $file->name;
            if (!file_exists($rootPath)) {
                mkdir($rootPath, 0777, true);
            }
            $fileName = $rootPath.$randName;
            $file->saveAs($fileName);
            $data[] = [
                'name' => $str = str_replace('.'.$ext, '', $file->name),
                'ext' => $ext,
                'url' => 'http://'.$_SERVER['HTTP_HOST'].$filePath . $randName
            ];
        }
        return $data;
    }

    public function createApplyId()
    {

    }
}