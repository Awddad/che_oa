<?php

namespace app\modules\oa_v1\logic;


use app\models as appmodel;
use app\models\JieKuan;
use yii\data\Pagination;
/**
 * 申请单逻辑
 * @author Administrator
 *
 */
class ApplyLogic extends BaseLogic
{
	/**
	 * 获取申请列表
	 * @param unknown_type $search
	 * @param unknown_type $user
	 */
	public function getApplyList($search,$user)
	{
		$type = $search['type'];
		$page = isset($search['page']) ? (1 <= $search['page'] ? (int)$search['page'] : 1) : 1;
		$page_size = @$search['page_size'] ? : 20;
		$keywords = iconv(mb_detect_encoding(@$search['keywords'],"UTF-8,GB2312,GBK"),"UTF-8//IGNORE",@$search['keywords']);
		$keywords = trim($keywords);
		if(isset($search['at'])) {
            $apply_type = (array)@$search['at'];
        } 
		
		$query ;
		
		if(1 == $type){//待我审批
			$approval_model = new appmodel\ApprovalLog();
			$query = $approval_model::find()
			-> andWhere(['approval_person_id'=>$user['person_id'],'is_to_me_now'=>1])
			-> andWhere(['or', 'status=1', 'status=11'])
			-> joinWith('apply a',true,'RIGHT JOIN')
			-> orderBy('create_time');;
		}elseif(2 == $type){//我已审批
			$approval_model = new appmodel\ApprovalLog();
			$query = $approval_model::find()
			-> andWhere(['approval_person_id'=>$user['person_id'],'result'=>1])
			-> joinWith('apply a',true,'RIGHT JOIN')
			-> orderBy('create_time');
		}elseif(3 == $type){//我发起的
			$apply_model = new appmodel\Apply();
			$query = $apply_model::find()
			-> alias('a')
			-> Where(['person_id'=>$user['person_id']])
			-> orderBy('create_time');
		}elseif(4 == $type){//抄送给我的
			$copy_model = new appmodel\ApplyCopyPerson();
			$query = $copy_model::find()
			-> joinWith('apply a',true,'RIGHT JOIN')
			-> Where(['copy_person_id'=>$user['person_id']])
			-> orderBy('create_time');
		}else{
			return false;
		}
		//开始时间
		if(@$search['start_time']){
			$start_time = strtotime($search['start_time'].' 0:0:0');
			$query -> andWhere(['>','create_time',$start_time]);
		}
		//结束时间
		if(@$search['end_time']){
			$end_time = strtotime($search['end_time'].' 23:59:59');
			$query -> andWhere(['<','create_time',$end_time]);
		}
		//关键词
		if($keywords){
			$query -> andWhere("instr(CONCAT(a.apply_id,a.title,a.person,a.approval_persons,a.copy_person),'{$keywords}') > 0 ");
		}
		//状态
		switch(@$search['status']){
			case 1://审核中
				$query -> andWhere(['in','status',[1,11]]);
				break;
			case 2://财务确认中
				$query -> andWhere(['status'=>4]);
				break;
			case 3://撤销
				$query -> andWhere(['status'=>3]);
				break;
			case 4://审核不通过
				$query -> andWhere(['status'=>2]);
				break;
			case 5://完成
				$query -> andWhere(['status'=>99]);
				break;
			default:
				break;
		}
		//类型
		if($apply_type){
			$query -> andWhere(['in','a.type' , $apply_type]);
		}
		
		$_query = clone $query;
		//var_dump($_query -> createCommand()->getRawSql());die();
		$total = $_query -> count();
		//var_dump($total);die();
		$pagination = new Pagination(['totalCount' => $total]);
		//当前页
		$pagination -> setPage($page-1);
		//每页显示条数
		$pagination->setPageSize($page_size, true);
		//排序
		switch(@$search['sort']){
			case 'asc'://时间顺序
				$orderBy = ['create_time'=>SORT_ASC];
				break;
			default://时间倒序
				$orderBy = ['create_time'=>SORT_DESC];
				break;
		}
		
		$query -> select('*') -> orderBy($orderBy) -> offset($pagination->getPage() * $pagination->pageSize)->limit($pagination->getLimit());
		//var_dump($query -> createCommand()->getRawSql());die();
		$res = $query -> asArray() -> all();
		//var_dump($res);die();
		
		return [
			'data' => $res,
			'pages' => $this->pageFix($pagination)
		];
		
	}
	/**
	 * 获取申请详情
	 * @param int $apply_id 审批号
	 * @param int $type 审批类型
	 */
	public function getApplyInfo($apply_id,$type = null)
	{
		$app_model = new appmodel\Apply();
		$apply = $app_model::find() -> where(['apply_id'=>$apply_id]) -> asArray() -> one() ;
		if(!$apply || ($type && $type != $apply['type'])){
			return false;
		}
		$caiwu = ['shoukuan'=>[],'fukuan'=>[]];
		switch($apply['type']){
			case 1://报销
				$info = $this -> getBaoxiaoInfo($apply_id);
				$caiwu['fukuan'] = $this -> getFukuanInfo($apply_id);
				break;
			case 2://借款
				$info = $this -> getJiekuanInfo($apply_id);
				$caiwu['fukuan'] = $this -> getFukuanInfo($apply_id);
				break;
			case 3://还款
				$info = $this -> getPaybackInfo($apply_id);
				$caiwu['shoukuan'] = $this -> getShoukuanInfo($apply_id);
				break;
			default:
				break;
		}
		$apply['info'] = $info;
		$apply['caiwu'] = $caiwu;
		$apply['approval'] =  $this -> getApproval($apply_id);;
		$apply['copy_person'] = $this -> getCopyPerson($apply_id);
		return $apply;
	}
	/**
	 * 报销明细
	 * @param int $apply_id
	 */
	public function getBaoxiaoInfo($apply_id)
	{
		$model = new appmodel\BaoXiao();
		$_model = new appmodel\BaoXiaoList();
		$info = $model::find() -> where(['apply_id' => $apply_id]) -> asArray() -> one();
		if($info['bao_xiao_list_ids'])
			$info['list'] = $_model::find() -> where("id in ({$info['bao_xiao_list_ids']})") -> asArray() -> all();;
		return $info;
	}
	/**
	 * 借款明细
	 * @param int $apply_id
	 */
	public function getJiekuanInfo($apply_id)
	{
		$model = new appmodel\JieKuan();
		$info = $model::find() -> where(['apply_id'=>$apply_id]) -> asArray() -> one();
		return $info;
	}
	/**
	 * 还款明细
	 * @param int $apply_id
	 */
	public function getPaybackInfo($apply_id)
	{
		$model = new appmodel\PayBack();
		$info = $model::find() -> where(['apply_id'=>$apply_id]) -> asArray() -> one();
		if($info['jie_kuan_ids']){
			$_model = new appmodel\JieKuan();
			$info['list'] = $_model::find() -> where("apply_id in ({$info['jie_kuan_ids']})") -> asArray() -> all();
		}
		return $info;
	}
	/**
	 * 财务付款信息
	 * @param int $apply_id
	 */
	public function getFukuanInfo($apply_id)
	{
		$model = new appmodel\CaiWuFuKuan();
		$fukuan = $model::find() -> where(['apply_id' => $apply_id]) -> asArray() -> one();
		return $fukuan;
	}
	/**
	 * 财务收款信息
	 * @param int $apply_id
	 */
	public function getShoukuanInfo($apply_id)
	{
		$model = new appmodel\CaiWuShouKuan();
		$shoukuan = $model::find() -> where(['apply_id' => $apply_id]) -> asArray() -> one();
		return $shoukuan;
	}
	/**
	 * 审批人信息
	 * @param int $apply_id
	 */
	public function getApproval($apply_id)
	{
		$model = new appmodel\ApprovalLog();
		$approval = $model::find() -> where(['apply_id' => $apply_id]) -> asArray() -> all();
		return $approval;
	}
	/**
	 * 抄送人信息
	 * @param int $apply_id
	 */
	public function getCopyPerson($apply_id)
	{
		$model = new appmodel\ApplyCopyPerson();
		$copy_person = $model::find() -> where(['apply_id' => $apply_id]) -> asArray() -> all();
		return $copy_person;
	}
}