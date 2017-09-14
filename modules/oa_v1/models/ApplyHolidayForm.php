<?php
/**
 * Created by PhpStorm.
 * User: YJR
 * Date: 2017/9/14
 * Time: 17:16
 */

namespace app\modules\oa_v1\models;

use app\models\ApplyHoliday;
use Yii;
use yii\validators\DateValidator;

class ApplyHolidayForm extends BaseForm
{
    public $type = 18;
    public $cai_wu_need = 1;

    public $apply_id;
    public $h_type;
    public $time;
    public $duration;
    public $des;
    public $files;
    public $approval_persons;
    public $copy_person;

    protected $_type = [
        1=>'病假',
        2=>'事假',
        3=>'年假',
        4=>'调休',
        5=>'陪产假',
        6=>'婚假',
        7=>'丧假',
        8=>'产检假',
        9=>'产假',
    ];

    public function rules()
    {
        return [
            [
                ['apply_id', 'h_type', 'des', 'time', 'des', 'duration', 'approval_persons'],
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
            ['h_type','in', 'range'=>array_keys($this->_type), 'message'=>'证件类型不正确'],
            ['duration','integer','message'=>'休假时长不正确！'],
            ['des','string','max' => 1000,'message' => '说明不正确！'],
            ['apply_id', 'unique','targetClass'=>'\app\models\Apply', 'message'=> '申请单已存在'],
            ['time','checkTime'],
            ['files','safe'],
        ];
    }

    public function checkTime($attribute)
    {
        if(!$this->hasErrors()){
            $validator = new DateValidator(['format'=>'yyyy-mm-dd HH:mm']);
            $time = explode(' - ',$this->$attribute);
            foreach($time as $v){
                if(!$validator->validate($v)){
                    $this->addError($attribute,'休假时间不正确');
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
            $this->saveHoliday($apply);
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
    public function saveHoliday($apply)
    {
        $time = explode(' - ',$this->time);
        $model = new ApplyHoliday();
        $model->apply_id = $apply->apply_id;
        $model->type = $this->h_type;
        $model->type_name = $this->_type[$this->h_type];
        $model->start_time = $time[0];
        $model->end_time = $time[1];
        $model->duration = $this->duration;
        $model->des = $this->des;
        $model->files = $this->files ? json_encode($this->files) : '';
        $model->created_at = time();

        if(!$model->save()){
            throw new Exception(current($model->getFirstErrors()));
        }
    }

    /**
     * 获得休假类型
     * @return array
     */
    public function getHolidayType()
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