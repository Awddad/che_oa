<?php
namespace app\modules\oa_v1\controllers;

use Yii;
use yii\base\Controller;
use app\models as appmodel;
use app\modules\oa_v1\logic\TypeLogic;

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
	
	
	
}