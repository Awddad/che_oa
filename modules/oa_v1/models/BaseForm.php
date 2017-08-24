<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/5
 * Time: 11:21
 */

namespace app\modules\oa_v1\models;


use app\logic\MyTcPdf;
use app\models\Person;
use app\modules\oa_v1\logic\PersonLogic;
use app\models\Apply;
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
     * 是否需要财务确认
     * @var
     */
    public $cai_wu_need = 1;
    
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
        2 => '备用金',
        3 => '还款',
        4 => '付款',
        5 => '请购',
        6 => '需求单',
        7 => '用章',
        8 => '固定资产领用',
        9 => '固定资产归还',
    	10 => '转正',
    	11 => '离职',
    	12 => '调职',
        13 => '开店',
        14 => '商品上架',
    ];
    
    
    /**
     * @param $attribute
     */
    public function checkTotal($attribute) {
        if (count($this->$attribute) > 7) {
            if($attribute == 'approval_persons')
                $this->addError($attribute, '审批人不能超过7个');
            else
                $this->addError($attribute, '抄送人不能超过7个');
        }
    }

    /**
     * @param $attribute
     */
    public function checkOnly($attribute) {
        if(strlen($this->$attribute) != 19) {
            $this->addError($attribute, '申请单格式错误');
        }
        if (Apply::findOne($this->$attribute)) {
            $this->addError($attribute, '申请单已存在');
        }
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
        $approval_persons = array_unique($this->approval_persons);
        foreach ($approval_persons as $v) {
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
        if(is_array($this->$type)){
	        foreach ($this->$type as $v) {
	            $person[] = PersonLogic::instance()->getPersonName($v);
	        }
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
        if(is_array($this->copy_person)){
            $copy_person = array_unique($this->copy_person);
	        foreach ($copy_person as $v) {
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
        if(empty($files)) {
            return false;
        }
        $basePath = \Yii::$app->basePath.'/web';

        $filePath = static::$FILES_PATH.date('Y-m-d').'/';

        $rootPath = $basePath.$filePath;
        $data = [];
        foreach ($files as  $file) {
            $ext = $file->getExtension();
            if (!in_array($ext, ['doc','xlsx','pdf'])) {
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
            $data[] = 'http://'.$_SERVER['HTTP_HOST'].$filePath . $randName;
        }
        return implode(',', $data);
    }

    /**
     * 创建申请ID
     * 审批单编号生成规则
     * 201705031617            01/02/03              089
     * 具体到秒的时间戳           申请类型         随机三位数
     *
     * @param $type
     *
     * @return string
     */
    public function createApplyId($type = null)
    {
        $type = $type ? : $this->type;
        if ($type  < 10) {
            $type = '0'.$type;
        }
        return date('YmdHis'). $type . $this->getRandNum();
    }

    /**
     * 随机数
     *
     * @param int $length
     * @return string
     */
    public function getRandNum($length = 3)
    {
        $num = rand(0, pow(10, $length) - 1);
        return str_pad($num, $length, 0, STR_PAD_LEFT);
    }

    /**
     * 创建申请标题
     *
     * @param $user
     * @return string
     */
    public function createApplyTitle($user)
    {
        return $user['person_name'] . '的' . Apply::TYPE_ARRAY[$this->type] . '申请';
    }
    
    /**
     * 创建PDF
     *
     * @param $param
     * @param $type
     *
     * @return string
     */
    public function createPdf($param, $type)
    {
        $pdf = new  MyTcPdf();
        $basePath = \Yii::$app->basePath.'/web';
        $filePath = '/upload/pdf/'.date('Y-m-d').'/';
        $rootPath = $basePath.$filePath;
        if (!file_exists($rootPath)) {
            @mkdir($rootPath, 0777, true);
        }
        $rst = $pdf->createdPdf($rootPath.$this->apply_id.'.pdf', $param, $type);
        if ($rst) {
            $pdfUrl = $filePath.$this->apply_id.'.pdf';
        } else {
            $pdfUrl = '';
        }
        return $pdfUrl;
    }
    
    /**
     * 获取Asset 对象
     *
     * @param Person $person
     *
     * @return Apply
     */
    public function setApply($person)
    {
        $applyId = $this->apply_id;
        $pdfUrl = '';
        $nextName = PersonLogic::instance()->getPersonName($this->approval_persons[0]);
        
        $apply = new Apply();
        $apply->apply_id = $applyId;
        $apply->title = $this->createApplyTitle($person);
        $apply->create_time = $_SERVER['REQUEST_TIME'];
        $apply->type = $this->type;
        $apply->person_id = $person->person_id;
        $apply->person = $person->person_name;
        $apply->status = 1;
        $apply->next_des = '等待'.$nextName.'审批';
        $apply->approval_persons = $this->getPerson('approval_persons');
        $apply->copy_person = $this->getPerson('copy_person');
        $apply->apply_list_pdf = $pdfUrl;
        $apply->cai_wu_need = $this->cai_wu_need;
        $apply->org_id = $person->org_id;
        $apply->company_id = $person->company_id;
        return $apply;
    }
}