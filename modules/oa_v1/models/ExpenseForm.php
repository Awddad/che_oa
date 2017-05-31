<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/4
 * Time: 11:11
 */

namespace app\modules\oa_v1\models;


use app\logic\TypeLogic;
use app\models\Apply;
use app\models\BaoXiao;
use app\models\Person;
use yii\base\Model;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;


/**
 * Class ExpenseForm
 * @package app\modules\oa_v1\models
 *
 * @property array $list
 * @property string $apply_id
 * @property integer $type
 * @property string $title
 * @property array $approval_persons
 * @property array $copy_person
 * @property string $money
 * @property string $files
 * @property string $pics
 * @property string $bank_card_id
 * @property string $bank_name
 * @property string $bank_name_des
 */
class ExpenseForm extends Model
{
    public $list;

    public $apply_id;

    public $type;

    public $title;

    public $approval_persons = [];

    public $copy_person = [];

    public $money;

    public $files;

    public $pics;

    public $bank_card_id;

    public $bank_name;

    public $bank_name_des;


    public function rules()
    {
        return [
            [['type'], 'integer'],
            [['type', 'approval_persons', 'copy_person', 'list','bank_card_id', 'bank_name'], 'required', 'message' => '*缺少必填字段'],
            [['title', 'apply_id', 'bank_card_id', 'bank_name', 'bank_name_des'], 'string'],
            [['list'], 'validateList'],
            [['approval_persons', 'copy_person'], 'each', 'rule' => ['integer']],
            [[ 'files', 'pics'], 'each', 'rule' => ['safe']],
        ];
    }

    /**
     * 报销明细验证
     *
     * @param $attribute
     */
    public function validateList($attribute)
    {
        foreach ($this->$attribute as $v){
            if(!isset($v['money']) || !isset($v['type'])) {
                $this->addError($attribute, '报销明细，字段错误');
            }
        }
    }

    public function createApplyId()
    {

    }

    public function save()
    {
        return $this;
        $user = \Yii::$app->session->get('userinfo');
        $apply = new Apply();
        $apply->apply_id = $this->createApplyId();
        $apply->title = $this->title;
        $apply->create_time = $_SERVER['REQUEST_TIME'];
        $apply->type = $this->type;
        $apply->person_id = $user['person_id'];
        $apply->person = $user['person_name'];
        $apply->status = 1;
        $apply->next_des = '等待'.$this->getPersonName($this->approval_persons[0]).'审批';
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();
        try{
            if (!$apply->save()) {
                new Exception('申请失败');
            }
            $this->saveList($apply);
            $this->approvalPerson($apply);
            $this->copyPerson($apply);
            $this->expense($apply);
            $transaction->commit();

        } catch (Exception $exception){
            $transaction->rollBack();
            throw $exception;
        }
        return $this;
    }

    /**
     * 根据ID得到用户姓名
     *
     * @param $personId
     * @return string
     */
    public function getPersonName($personId)
    {
        return Person::findOne($personId)->person_name;
    }

    /**
     * 报销明细
     *
     * @param Apply $apply
     */
    public function saveList($apply)
    {
        $data = [];
        foreach ($this->list as $v) {
            $data[] = [
                $apply->apply_id,
                $v['money'],
                $v['type'],
                TypeLogic::instance()->getTypeName($v['type']),
                $v['des']
            ];
        }
        \Yii::$app->db->createCommand()->batchInsert('oa_bao_xiao_list',[
            'apply_id', 'money', 'type', 'type_name', 'des'
        ],$data)->execute();
    }

    public function getTotalMoney()
    {
        return array_sum(ArrayHelper::getColumn($this->list, 'money'));
    }

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
            $end = $i == $count ? 1 : 0;
            $begin = $i == 1 ? : 0;
            $data[] = [
                $apply->apply_id,
                $this->getPersonName($v['copy_person_id']),
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
            $data[] = [
                $apply->apply_id,
                $v['copy_person_id'],
                $this->getPersonName($v['copy_person_id']),
            ];
        }
        \Yii::$app->db->createCommand()->batchInsert('oa_approval_log',[
            'apply_id', 'copy_person_id', 'copy_person',
        ],$data);
    }

    /**
     * 报销单
     *
     * @param Apply $apply
     * @return BaoXiao
     */
    public function expense($apply)
    {
        $model = new BaoXiao();
        $model->apply_id = $apply->apply_id;
        $model->bank_name = $this->bank_name;
        $model->bank_card_id = $this->bank_card_id;
        $model->bank_name_des = $this->bank_name_des;
        $model->files = $this->files;
        $model->pics = $this->pics;
        $model->money = $this->getTotalMoney();
        if ($model->save()) {
            new Exception('报销单生成失败');
        }
        return $model;
    }

    /**
     * 保存文件
     *
     * @param $name
     * @return array
     */
    public function saveUploadFile($name)
    {
        $files = UploadedFile::getInstancesByName($name);
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

}