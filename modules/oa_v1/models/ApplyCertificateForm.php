<?php
/**
 * Created by PhpStorm.
 * User: YJR
 * Date: 2017/9/14
 * Time: 11:29
 */

namespace app\modules\oa_v1\models;

use Yii;
use app\models\Apply;
use app\models\ApplyCertificate;
use yii\validators\DateValidator;

class ApplyCertificateForm extends BaseForm
{
    public $type = 17;
    public $cai_wu_need = 1;

    public $apply_id;
    public $c_type;
    public $org_id;
    public $use_time;
    public $des;
    public $files;
    public $approval_persons;
    public $copy_person;

    protected $_type = [
        1=>'营业执照正本原件',
        2=>'营业执照副本原件',
        3=>'开户许可证原件',
        4=>'章程',
    ];

    public function rules()
    {
        return [
            [
                ['apply_id', 'c_type', 'org_id', 'des', 'use_time', 'des', 'approval_persons'],
                'required',
                'message'=>'{attribute}不能为空'
            ],
            [
                ['approval_persons', 'copy_person'],
                'each',
                'rule' => ['integer']
            ],
            [
                ['approval_persons', 'copy_person'], 'checkTotal'
            ],
            ['c_type','in', 'range'=>array_keys($this->_type), 'message'=>'证件类型不正确'],
            ['org_id','exist','targetClass'=>'\app\models\Org','targetAttribute'=>'org_id','message'=>'组织不存在！'],
            ['des','string','max' => 1000,'message' => '说明不正确！'],
            ['apply_id', 'unique','targetClass'=>'\app\models\Apply', 'message'=> '申请单已存在'],
            ['use_time','checkUseTime'],
            ['files','safe'],
        ];
    }

    public function checkUseTime($attribute)
    {
        if(!$this->hasErrors()){
            $validator = new DateValidator(['format'=>'yyyy-mm-dd']);
            $time = explode(' - ',$this->$attribute);
            foreach($time as $v){
                if(!$validator->validate($v)){
                    $this->addError($attribute,'使用时间不正确');
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public function saveApply($user)
    {
        $apply = $this->setApply($user);

        $transaction = Yii::$app->db->beginTransaction();
        try{
            if(!$apply->save()){
                throw new Exception(current($apply->getFirstErrors()));
            }
            $this->saveCertificate($apply);
            $this->approvalPerson($apply);
            $this->copyPerson($apply);
            $transaction->commit();
            return ['status'=>true,'apply_id'=>$this->apply_id];
        }catch(Exception $e){
            $transaction->rollBack();
            return ['status'=>false,'msg'=>$e->getMessage()];
        }
    }

    /**
     * @param $apply Apply
     */
    public function saveCertificate($apply)
    {
        $time = explode(' - ',$this->use_time);
        $model = new ApplyCertificate();
        $model->apply_id = $apply->apply_id;
        $model->type = $this->c_type;
        $model->type_name = $this->_type[$this->c_type];
        $model->org_id = $this->org_id;
        $model->start_time = $time[0];
        $model->end_time = $time[1];
        $model->des = $this->des;
        $model->files = $this->files ? json_encode($this->files) : '';
        $model->created_at = time();

        if(!$model->save()){
            throw new Exception(current($model->getFirstErrors()));
        }
    }

    /**
     * 获得证件类型
     * @return array
     */
    public function getCertificateType()
    {
        $data = [];
        foreach($this->_type as $k=>$v){
            $data[] = [
                'label'=>$v,
                'value'=>$k,
            ];
        }
        return $data;
    }
}