<?php
namespace app\modules\oa_v1\controllers;

use Yii;
use yii\base\Controller;
use app\models as appmodel;
use app\modules\oa_v1\logic\TypeLogic;
use app\modules\oa_v1\models\Apply;

class ApplyController extends BaseController
{
	private $page_size = 20;
	
	public function actionGetList()
	{
		$get = Yii::$app -> request -> get();
		$type = $get['type'];
		$page = isset($get['page']) ? (1 <= $get['page'] ? (int)$get['page'] : 1) : 1; 
		$keywords = iconv(mb_detect_encoding(@$get['keywords'],"UTF-8,GB2312,GBK"),"UTF-8//IGNORE",@$get['keywords']); 
		
		$query ;
		
		if(1 == $type){//待我审批
			$approval_model = new appmodel\ApprovalLog();
			$query = $approval_model::find() 
									-> andWhere(['approval_person_id'=>$this -> arrPersonInfo['person_id'],'is_to_me_now'=>1])
									-> andWhere(['or', 'status=1', 'status=11'])
									-> joinWith('apply a')
									-> orderBy('create_time');;
		}elseif(2 == $type){//我已审批
			$approval_model = new appmodel\ApprovalLog();
			$query = $approval_model::find() 
									-> andWhere(['approval_person_id'=>$this -> arrPersonInfo['person_id'],'result'=>1])
									-> joinWith('apply a')
									-> orderBy('create_time');
		}elseif(3 == $type){//我发起的
			$apply_model = new appmodel\Apply();
			$query = $apply_model::find()
								-> alias('a')
								-> Where(['person_id'=>$this -> arrPersonInfo['person_id']])
								-> orderBy('create_time');
		}elseif(4 == $type){//抄送给我的
			$copy_model = new appmodel\ApplyCopyPerson();
			$query = $copy_model::find()
								-> joinWith('apply a')
								-> Where(['copy_person_id'=>$this -> arrPersonInfo['person_id']])
								-> orderBy('create_time');
		}else{
			return $this -> _return('type不正确',403);
		}
		if(@$get['start_time']){
			$start_time = strtotime($get['start_time'].' 0:0:0');
			$query -> andWhere(['>','create_time',$start_time]);
		}
		if(@$get['end_time']){
			$end_time = strtotime($get['end_time'].' 23:59:59');
			$query -> andWhere(['<','create_time',$end_time]);
		}
		if($keywords){
			$query -> andWhere("instr(CONCAT(a.apply_id,a.title,a.person,a.approval_persons,a.copy_person),'{$keywords}') > 0 ");
		}
		$_query = clone $query;
		$query -> select('*') -> offset($page-1)->limit($this -> page_size);
        //var_dump($query -> createCommand()->getRawSql());die();
		$res = $query -> asArray() -> all();
		$total = $_query -> count();
		//var_dump($res,$total);die();
		$data = ['total'=>$total,'res'=>[]];
		foreach($res as $v){
			$data['res'][] = [
						'apply_id' => $v['apply_id'],//审批单编号
						'date' => date('Y-m-d h:i:s',$v['create_time']),//创建时间
						'type' => $v['type'] ,//类型
						'type_value' => $this -> type[$v['type']],//类型值
						'title' => $v['title'],//标题
						'person' => $v['person'],//发起人
						'approval_persons' => str_replace(',', ' -> ', $v['approval_persons']),//审批人
						'copy_person' => $v['copy_person'],//抄送人
						'status' => $v['status'],//状态
						'next_des' => $v['next_des'],//下步说明
						'can_cancel' => in_array($v['status'], [1,11]) ? 1 : 0,//是否可以撤销
					  ];
		}
		return $this -> _return(json_encode($data,JSON_UNESCAPED_UNICODE),200);
		
	} 
	/**
	 * 报销详情
	 */
	public function actionGetBaoxiao()
	{
		$get = Yii::$app -> request -> get();
		$apply_id = trim(@$get['apply_id']);
		if(!$apply_id){
			return $this -> _return("参数不能为空",403);
		}
		$model = new Apply();
		$apply = $model -> getApplyInfo($apply_id,1);
		if(!$apply){
			return $this -> _return('报销单不存在！',403);
		}
		$data = $this -> getData($apply);
		$data['info'] = [
				'money' => $apply['info']['money'],
				'bank_card_id' => $apply['info']['bank_card_id'],
				'bank_name' => $apply['info']['bank_name'].$apply['info']['bank_name_des'],
				'file' => explode(',', $apply['info']['files']),
				'pics' => explode(',', $apply['info']['pics']),
				'pdf' => $apply['info']['bao_xiao_dan_pdf'],
				'list' => [],
		];
		foreach($apply['info']['list'] as $v){
			$data['info']['list'][] = [
				'money' => $v['money'],
				'type_name' => $v['type_name'],
				'type' => $v['type'],
				'desc' => $v['des']
			];
		}
		if($apply['caiwu']['fukuan']){
			$data['caiwu'] = $this -> getFukuanData($apply);
		}
		return $this -> _return(json_encode($data,JSON_UNESCAPED_UNICODE),200);
	}
	/**
	 * 借款详情
	 */
	public function actionGetJiekuan()
	{
		$get = Yii::$app -> request -> get();
		$apply_id = trim(@$get['apply_id']);
		if(!$apply_id){
			return $this -> _return("参数不能为空",403);
		}
		$model = new Apply();
		$apply = $model -> getApplyInfo($apply_id,2);
		if(!$apply){
			return $this -> _return('借款单不存在！',403);
		}
		$data = $this -> getData($apply);
		$data['info'] = [
			'money' => $v['money'],
			'bank_card_id' => $apply['info']['bank_card_id'],
			'bank_name' => $apply['info']['bank_name'].$apply['info']['bank_name_des'],
			'tips' => $apply['info']['tips'],
			'des' => $apply['info']['des'],
			'pics' => implode(',',$apply['info']['pics']),
			'is_pay_back' => $apply['info']['is_pay_back'],
		];
		if($apply['caiwu']['fukuan']){
			$data['caiwu'] = $this -> getFukuanData($apply);
		}
		return $this -> _return(json_encode($data,JSON_UNESCAPED_UNICODE),200);
	}
	/**
	 * 还款信息
	 */
	public function actionGetPayback()
	{
		$get = Yii::$app -> request -> get();
		$apply_id = trim(@$get['apply_id']);
		if(!$apply_id){
			return $this -> _return("参数不能为空",403);
		}
		$model = new Apply();
		$apply = $model -> getApplyInfo($apply_id,3);
		if(!$apply){
			return $this -> _return('还款单不存在！',403);
		}
		$data = $this -> getData($apply);
		$data['info'] = [
			'money' => $v['money'],
			'bank_card_id' => $apply['info']['bank_card_id'],
			'bank_name' => $apply['info']['bank_name'].$apply['info']['bank_name_des'],
			'des' => $apply['info']['des'],
			'pics' => implode(',',$apply['info']['pics']),
			'is_pay_back' => $apply['info']['is_pay_back'],
			'list'=>[],
			];
		foreach($apply['info']['list'] as $v){
			$data['info']['list'][] = [
				'money' => $v['money'],
				'time' => date('Y-m-d h:i:s',$v['get_money_time']),
				'des' => $v['des']
			];
		}
		
		if($apply['caiwu']['shoukuan']){
			$data['caiwu'] = $this -> getShoukuanData($apply);
		}
		return $this -> _return(json_encode($data,JSON_UNESCAPED_UNICODE),200);
	}
	
	
	
	
	
	protected function getData($apply)
	{
		$data = [
				'apply_id' => $apply['apply_id'],
				'create_time' => $date('Y-m-d h:i:s',$apply['create_time']),
				'next_des' => $apply['next_des'],
				'title' => $apply['title'],
				'type' => $apply['type'],
				'type_value' => $this -> type[$apply['type']],
				'person' => $apply['person'],
				//'person_id' => $apply['person_id'],
				'copy_person' => [],
				'approval' => [],
			];
		foreach($apply['copy_person'] as $v){
			$data['copy_person'][] = [
										'person_id'=>$v['copy_person_id'],
										'person'=>$v['copy_person']
									];
		}
		foreach($apply['approval'] as $v){
			$data['approval'][] = [
									'person_id' => $v['approval_person_id'],
									'person' => $v['approval_person'],
									'steep'	=> $v['steep'],
									'result' => $v['result'],
									'time' => date('Y-m-d h:i:s',$v['approval_time']),
									'des' => $v['des'],
								];
		}
		return $data;
	}
	protected function getFukuanData($apply)
	{
		$data = [
				'org_name' => $apply['caiwu']['fukuan']['org_name'],
				'des' => $apply['caiwu']['fukuan']['tips'],
				'time' => date('Y-m-d h:i:s',$apply['caiwu']['fukuan']['fu_kuan_time']),
				];
		return $data;
	}
	protected function getShoukuanData($apply)
	{
		$data = [
			'org_name' => $apply['caiwu']['shoukuan']['org_name'],
			'time' => date('Y-m-d h:i:s',$apply['caiwu']['shoukuan']['shou_kuan_time']),
			'tips' => $apply['caiwu']['shoukuan']['tips'],
		];
	}
}