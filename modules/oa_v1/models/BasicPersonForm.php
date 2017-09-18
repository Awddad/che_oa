<?php
namespace app\modules\oa_v1\models;

use app\models\PersonType;
use yii\helpers\ArrayHelper;
use yii\data\Pagination;
use app\modules\oa_v1\logic\BackLogic;

class BasicPersonForm extends BaseForm
{
	const SCENARIO_ADD = 'add';
	const SCENARIO_EDIT = 'edit';
	
	public $id;
	public $name;
	
	public function rules()
	{
		return [
				[['name'],'required','message'=>'{attribute}不能为空','on'=>[self::SCENARIO_ADD]],
				[['id','name'],'required','message'=>'{attribute}不能为空','on'=>[self::SCENARIO_EDIT]],
				
				['id','exist','targetClass'=>'\app\models\PersonType','message'=>'原数据不存在！'],
				['name','string','length' => [1, 20],'message'=>'名称不正确'],
		];
	}
	public function scenarios()
	{
		return [
				self::SCENARIO_ADD => ['name'],
				self::SCENARIO_EDIT => ['id','name'],
		];
	}
	
	public function editType()
	{
		$model = PersonType::findOne($this->id);
		if(empty($model)){
			$model = new PersonType();
		}
		$model->name = $this->name;
		if($model->save()){
			return ['status'=>true];
		}else{
			return ['status'=>false,'msg'=>current($model->getFirstErrors())];
		}
	}
	
	public function getTypeList($params)
	{
		$keywords = trim(ArrayHelper::getValue($params,'keywords',null));
		$start_time = ArrayHelper::getValue($params,'start_time',null);
		$end_time = ArrayHelper::getValue($params,'end_time',null);
		//$page = ArrayHelper::getValue($params,'page',1);
		$page_size = ArrayHelper::getValue($params,'page_size',10);
	
		$query = PersonType::find();
		//关键词
		if($keywords){
			$keywords = mb_convert_encoding($keywords,'UTF-8','auto');
			$query->andWhere(['like', 'name', $keywords]);
		}
		//开始时间
		if($start_time){
			$start_time = strtotime($start_time);
			$query->andWhere(['>=', 'update_time', $start_time]);
		}
		//结束时间
		if($end_time){
			$end_time = strtotime($end_time.' 23:59:59');
			$query->andWhere(['<=', 'update_time', $end_time]);
		}
		//分页
		$pagination = new Pagination([
				'defaultPageSize' => $page_size,
				'totalCount' => $query->count(),
		]);
	
		$data = $query->orderBy("update_time desc")
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