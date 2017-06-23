<?php
namespace app\modules\oa_v1\models;

use yii\helpers\ArrayHelper;
use app\models\AssetType;
use yii\data\Pagination;
use app\modules\oa_v1\logic\BackLogic;
use app\models\AssetBrand;

class BasicAssetForm extends BaseForm
{
	const SCENARIO_ADD_TYPE = 'add_type';
	const SCENARIO_ADD_CHILD = 'add_child';
	const SCENARIO_EDIT_TYPE = 'edit_type';
	const SCENARIO_ADD_BRAND = 'add_brand';
	const SCENARIO_EDIT_BRAND = 'edit_brand';
	
	public $type_id;
	public $type_name;
	public $child;
	public $brand_id;
	public $brand_name;
	
	public function rules()
	{
		return [
				[
					['type_name'],
					'required',
					'on'=>[self::SCENARIO_ADD_TYPE],
					'message'=>'{attribute}不能为空',
				],
				[
					['type_id','type_name'],
					'required',
					'on'=>[self::SCENARIO_EDIT_TYPE],
					'message'=>'{attribute}不能为空',
				],
    		    [
        		    ['type_id','child'],
        		    'required',
        		    'on'=>[self::SCENARIO_ADD_CHILD],
        		    'message'=>'{attribute}不能为空',
        		    ],
				[
					['brand_name'],
					'required',
					'on'=>[self::SCENARIO_ADD_BRAND],
					'message'=>'{attribute}不能为空',
				],
				[
					['brand_id','brand_name'],
					'required',
					'on'=>[self::SCENARIO_EDIT_BRAND],
					'message'=>'{attribute}不能为空',
				],
				
				
				['type_id','exist','targetClass'=>'\app\models\AssetType','targetAttribute'=>'id','message'=>'原数据不存在！'],
				['brand_id','exist','targetClass'=>'\app\models\AssetBrand','targetAttribute'=>'id','message'=>'原数据不存在！'],
				['type_name','string','length' => [2, 20],'message'=>'名称不正确'],
				['brand_name','string','length' => [2, 20],'message'=>'名称不正确'],
				//['child','safe'],
				//['child','each','rule'=>['string','length'=>[2, 20]],'message'=>'名称错误'],
				//['child','each','rule'=>['validatorChild']],
				//['child','string','message'=>'child不正确！'],
				
		];
	}
	
	public function validatorChild($attribute)
	{
		if(!$this->hasErrors()){
			$tmp = $this->$attribute;
			if(!$tmp['name']){
				$this->addError($attribute, 'child名称不正确！');
			}
		}	
	}
	
	public function scenarios()
	{
		return [
				self::SCENARIO_ADD_TYPE => ['type_name'],
		        self::SCENARIO_ADD_CHILD => ['type_id','child'],
				self::SCENARIO_EDIT_TYPE => ['type_id','type_name'],
				self::SCENARIO_ADD_BRAND => ['brand_name'],
				self::SCENARIO_EDIT_BRAND => ['brand_id','brand_name'],
		];
	}
	/**
	 * 添加资产类别
	 */
	public function addType()
	{
		//$transaction = \yii::$app->db->beginTransaction();
		$model = new AssetType();
		$model->name = $this->type_name;
		$model->parent_id = 0;
		if($model->save()){
		    return ['status'=>true];
		}else{
		    return ['status'=>false,'msg'=>current($model->getFirstErrors())];
		}
		/*
		try{
			if(!$model->save()){
				throw new \Exception(current($model->getFirstErrors()));
			}
			if($this->child){//有子类别
				$parent_id = $model->id;
				foreach ($this->child as $v) {
					$time = time();
					$data[] = [$v['name'],$time,$time,$parent_id,];
				}
				\Yii::$app->db->createCommand()->batchInsert('oa_asset_type',[
						'name', 'add_time', 'update_time', 'parent_id'
				],$data)->execute();
			}
			$transaction->commit();
			return ['status'=>true];
		}catch(\Exception $e){
			$transaction->rollBack();
			return ['status'=>false,'msg'=>$e->getMessage()];
		}
		*/
	}
	/**
	 * 修改资产类别
	 */
	public function updateType()
	{
		//$transaction = \yii::$app->db->beginTransaction();
		$model = AssetType::findOne($this->type_id);
		$model->name = $this->type_name;
		if($model->save()){
		    return ['status'=>true];
		}else{
		    return ['status'=>false,'msg'=>current($model->getFirstErrors())];
		}
		/*
		try{
			$model->save();
			if($model->hasErrors()){
				throw new \Exception(current($model->getFirstErrors()));
			}
			if($this->child){//有子类别
				$tmpModel = new AssetType();
				foreach($this->child as $v){
					$typeModel = AssetType::find()->where(['id'=>$v['id'],'parent_id'=>$model->id])->one();
					if(empty($typeModel)){
						$typeModel = clone $tmpModel;
						$typeModel->parent_id = $model->id;
					}
					$typeModel->name = $v['name'];
					if(!$typeModel->save()){
						throw new \Exception(current($typeModel->getFirstErrors()));
					}
				}
			}
			$transaction->commit();
			return ['status'=>true];
		}catch(\Exception $e){
			$transaction->rollBack();
			return ['status'=>false,'msg'=>$e->getMessage()];
		}
		*/
	}
	
	/**
	 * 添加子类
	 */
	public function addChild()
	{
	    $arr = explode("\n",$this->child);
	    foreach ($arr as $v) {
	        $time = time();
	        $data[] = [$v,$time,$time,$this->type_id,];
	    }
	    try{
    	    \Yii::$app->db->createCommand()->batchInsert('oa_asset_type',['name', 'add_time', 'update_time', 'parent_id'],$data)->execute();
    	    $assetType = AssetType::findOne($this->type_id);
    	    $assetType->has_child = 1;
    	    $assetType->save();
    	    return ['status'=>true];
	    }catch(\Exception $e){
			$transaction->rollBack();
			return ['status'=>false,'msg'=>$e->getMessage()];
		}
	}
	/**
	 * 添加品牌
	 */
	public function addBrand()
	{
		$model = new AssetBrand();
		$model->name = $this->brand_name;
		if($model->save()){
			return ['status'=>true];
		}else{
			return ['status'=>false,'msg'=>current($model->getFirstErrors())];
		}
	}
	/**
	 * 修改品牌
	 */
	public function updateBrand()
	{
		$model = AssetBrand::findOne($this->brand_id);
		$model->name = $this->brand_name;
		if($model->save()){
			return ['status'=>true];
		}else{
			return ['status'=>false,'msg'=>current($model->getFirstErrors())];
		}
	}
	/**
	 * 获得资产类别列表
	 * @param array $params
	 */
	public function getAssetTypeList($params)
	{
		$keywords = ArrayHelper::getValue($params,'keywords',null);
		$start_time = ArrayHelper::getValue($params,'start_time',null);
		$end_time = ArrayHelper::getValue($params,'end_time',null);
		$page = ArrayHelper::getValue($params,'page',1);
		$page_size = ArrayHelper::getValue($params,'page_size',10);
		$parent_id = ArrayHelper::getValue($params,'pid',0);
		
		$query = AssetType::find()->where(['parent_id' => $parent_id]);
		//关键词
		if($keywords){
			$keywords = mb_convert_encoding($keywords,'UTF-8','auto');
			$query->andWhere(['like', 'name', $keywords]);
		}
		//开始时间
		if($start_time){
			$start_time = strtotime($start_time);
			$query->addWhere(['>=', 'update_time', $start_time]);
		}
		//结束时间
		if($end_time){
			$end_time = strtotime($end_time);
			$query->addWhere(['<=', 'update_time', $end_time]);
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
	
	public function getAssetType($pid=0)
	{
	    $res = AssetType::find()->where(['parent_id'=>$pid])->all();
	    $data = [];
	    foreach($res as $v){
	        $tmp = [
	            'label' => $v->name,
                'value' => $v->id,
	        ];
	        $v->has_child && $tmp['children'] = $this->getAssetType($v->id);
	        $data[] = $tmp;
	    }
	    return $data;
	}
	
	/**
	 * 获得品牌列表
	 * @param array $params
	 */
	public function getAssetBrandList($params)
	{
		$keywords = ArrayHelper::getValue($params,'keywords',null);
		$start_time = ArrayHelper::getValue($params,'start_time',null);
		$end_time = ArrayHelper::getValue($params,'end_time',null);
		$page = ArrayHelper::getValue($params,'page',1);
		$page_size = ArrayHelper::getValue($params,'page_size',10);
		
		$query = AssetBrand::find();
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
			$end_time = strtotime($end_time);
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