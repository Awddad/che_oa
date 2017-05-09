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
     * 申请类型
     * @var
     */
    public $type;

    /**
     * @var array
     */
    public $typeArr = [
        1 => '报销',
        2 => '借款',
        3 => '还款'
    ];


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
            $personName = PersonLogic::instance()->getPersonName($v);
            $end = $i == $count ? 1 : 0;
            $begin = $i == 1 ? : 0;
            $data[] = [
                $apply->apply_id,
                $personName,
                $v,
                $i,
                $end,
                $begin
            ];
            $i++;
        }
        \Yii::$app->db->createCommand()->batchInsert('oa_approval_log',[
            'apply_id', 'approval_person', 'approval_person_id', 'steep', 'is_end','is_to_me_now'
        ],$data)->execute();
    }

    /**
     * 获取审批人或抄送人姓名
     * @param $type
     * @return string
     */
    public function getPerson($type)
    {
        $person = [];
        foreach ($this->$type as $v) {
            $person[] = PersonLogic::instance()->getPersonName($v);
        }
        return implode(',',$person);
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
            $personName = PersonLogic::instance()->getPersonName($v);
            $data[] = [
                $apply->apply_id,
                $v,
                $personName,
            ];
        }
        \Yii::$app->db->createCommand()->batchInsert('oa_apply_copy_person',[
            'apply_id', 'copy_person_id', 'copy_person',
        ],$data)->execute();
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

    /**
     * 创建申请ID
     *
     * @return string
     */
    public function createApplyId()
    {
        return date('YmdHis'). '0' .$this->type . rand(100, 999);
    }

    /**
     * 创建申请标题
     *
     * @param $user
     * @return string
     */
    public function createApplyTitle($user)
    {
        return $user['person_name'] . '的' . $this->typeArr[$this->type] . '申请';
    }
}