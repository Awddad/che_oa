<?php
namespace app\modules\oa_v1\models;

use yii;
use app\models\ApplyOpen;
use yii\db\Exception;
use yii\data\Pagination;
use app\modules\oa_v1\logic\BackLogic;
use app\modules\oa_v1\logic\RegionLogic;
use yii\helpers\ArrayHelper;

class ApplyOpenForm extends BaseForm
{
    public $type = 13;
    public $cai_wu_need = 1;
    
    
    public $apply_id;
    public $district;
    public $address;
    public $rental;
    public $summary;
    public $approval_persons;
    public $copy_person;
    public $files;
    
    public $district_type = 3;//district对应region的type值
    
    public function rules()
    {
        return [
            [
                ['apply_id','district','address','rental','summary','approval_persons'],
                'required',
                'message' => '{attribute}不能为空'
            ],
            [
                ['approval_persons', 'copy_person'],
                'each',
                'rule' => ['integer']
            ],
            [
                ['approval_persons', 'copy_person'], 'checkTotal'
            ],
            ['rental','number','numberPattern'=>'/(^[1-9]([0-9]+)?(\.[0-9]{1,2})?$)|(^(0){1}$)|(^[0-9]\.[0-9]([0-9])?$)/','message' => '租金不正确！'],
            ['summary','string','max'=>1024, 'message'=>'说明不正确！'],
            ['address','string','max'=>20, 'message'=>'地址不正确！'],
            ['district','exist','targetClass'=>'\app\models\Region','targetAttribute'=>['district'=>'id','district_type'=>'type'],'message'=>'区号不正确！'],
            ['apply_id', 'unique','targetClass'=>'\app\models\Apply', 'message'=> '申请单已存在'],
            ['files','safe'],
        ];
    }
    
    public function saveApply($user)
    {
        $apply = $this->setApply($user);
        
        $transaction = Yii::$app->db->beginTransaction();
        try{
            if(!$apply->save()){
                throw new Exception(current($apply->getFirstErrors()));
            }
            $this->saveOpen($apply);
            $this->approvalPerson($apply);
            $this->copyPerson($apply);
            $transaction->commit();
            return ['status'=>true,'apply_id'=>$this->apply_id];
        }catch(Exception $e){
            $transaction->rollBack();
            return ['status'=>false,'msg'=>$e->getMessage()];
        }
    }
    
    public function saveOpen($apply)
    {
        $model = new ApplyOpen();
        $model->apply_id = $this->apply_id;
        $model->district = $this->district;
        $model->district_name = RegionLogic::instance()->getRegionByChild($this->district);
        $model->address = $this->address;
        $model->rental = $this->rental;
        $model->files = $this->files?json_encode($this->files):'';
        $model->summary = $this->summary;
        $model->created_at = time();
        if(!$model->save()){
            throw new Exception(current($model->getFirstErrors()));
        }
        
    }
    /**
     * 开店列表
     * @param array $params
     */
    public function getOpenList($params)
    {
        $keywords = trim(ArrayHelper::getValue($params,'keywords',null));
        $start_time = ArrayHelper::getValue($params,'start_time',null);
        $end_time = ArrayHelper::getValue($params,'end_time',null);
        $page = ArrayHelper::getValue($params,'page',1);
        $page_size = ArrayHelper::getValue($params,'page_size',10);
        $status = ArrayHelper::getValue($params, 'status',null);
        $sort = ArrayHelper::getValue($params, 'sort','desc');
        
        $query = ApplyOpen::find()->joinWith('apply a',true,'RIGHT JOIN')->where(['type'=>13]);
        //关键词
        if($keywords){
            $keywords = trim(mb_convert_encoding($keywords,'UTF-8','auto'));
            $query -> andWhere("instr(CONCAT(district_name,address,person),'{$keywords}') > 0 ");
        }
        //开始时间
        if($start_time){
            $start_time = strtotime($start_time);
            $query->andWhere(['>=', 'create_time', $start_time]);
        }
        //结束时间
        if($end_time){
            $end_time = strtotime($end_time);
            $query->andWhere(['<=', 'create_time', $end_time]);
        }
        //状态
        if($status){
            $arr_status = [];
            foreach($status as $v){
                switch($v){
                    case 1://待确认
                        array_push($arr_status ,1,11);
                        break;
                    case 2://已通过
                        array_push($arr_status ,99);
                        break;
                    case 3://不通过
                        array_push($arr_status ,2);
                        break;
                    default:
                        break;
                }
            }
            if(count($arr_status) == 1){
                $query -> andWhere(['status'=>$arr_status[0]]);
            }elseif(count($arr_status) > 1){
                $query -> andWhere(['in','status',$arr_status]);
            }
        }
        //分页
        $pagination = new Pagination([
            'defaultPageSize' => $page_size,
            'totalCount' => $query->count(),
        ]);
        //排序
        switch($sort){
            case 'asc'://时间顺序
                $orderBy = ['create_time'=>SORT_ASC];
                break;
            default://时间倒序
                $orderBy = ['create_time'=>SORT_DESC];
                break;
        }
        $data = $query->orderBy($orderBy)->select('*')
        ->offset($pagination->offset)
        ->limit($pagination->limit)
        ->asArray()
        ->all();
        
        return [
            'data' => $data,
            'pages' => BackLogic::instance()->pageFix($pagination)
        ];
    }
     
}