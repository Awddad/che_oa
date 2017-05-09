<?php

namespace app\modules\oa_v1\models;

use Yii;
use app\models as appmodel;

class Apply extends \yii\base\Model
{
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