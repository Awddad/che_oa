<?php
namespace app\modules\oa_v1\models;



use app\modules\oa_v1\logic\BaseApplyLogic;
use app\models\Apply;
use app\models\JieKuan;

class ApplyView extends BaseForm
{
	protected  $typeMethod = [
			1 => 'Baoxiao',
			2 => 'Loan',
			3 => 'PayBack',
			4 => 'Pay',
			5 => 'Buy',
			6 => 'Demand',
			7 => 'UseChapter',
			8 => '固定资产零用',
			9 => '固定资产归还',
			10 => 'Positive',
			11 => '离职',
			12 => 'Transfer'
	];
	
	
	/**
	 * 获得申请详情
	 * @param \app\models\Apply $apply
	 * @return array
	 */
	public function getApply($apply)
	{
		$logic = BaseApplyLogic::instance();
		//基本信息
		$data = $logic->getBaseApply($apply);
		//流程
		$data['flow'] = $logic->getFlowData($apply);
		//申请信息
		$fuc = "get{$this->typeMethod[$apply['type']]}";
		$data['info'] = $this->$fuc($apply);
		return $data;
	}
	
	/**
	 * 报销详情
	 * @param \app\models\Apply $apply
	 * @return array
	 */
	protected function getBaoxiao($apply)
	{
		$baoxiao = $apply->expense;
		$data  = [
				'money' => $baoxiao->money,
				'bank_card_id' => $baoxiao->bank_card_id,
				'bank_name' => $baoxiao->bank_name,
				'bank_des' => $baoxiao->bank_name_des,
				'file' => json_decode($baoxiao->files),
				'pics' => explode(',', $baoxiao->pics),
				'list' => []
		];
		foreach ($baoxiao->list as $v) {
			$data['list'][] = [
					'money' => $v->money,
					'type_name' => $v->type_name,
					'type' => $v->type,
					'des' => $v->des
			];
		}
		return $data;
	}
	
	/**
	 * 借款详情
	 * @param \app\models\Apply $apply
	 * @return array
	 */
	protected function getLoan($apply)
	{
		$loan = $apply->loan;
		$data = [
			'money' => $loan->money,
			'bank_card_id' => $loan->bank_card_id,
			'bank_name' => $loan->bank_name,
			'bank_des' => $loan->bank_name_des,
			'tips' => $loan->tips,
			'des' => $loan->des,
			'pics' => explode(',', $loan->pics),
			'is_pay_back' => $loan->is_pay_back
		];
		return $data;
	}
	
	/**
	 * 还款详情
	 * @param \app\models\Apply $apply
	 * @return array
	 */
	protected function getPayBack($apply)
	{
		$payback = $apply->payBack;
		$data = [
			'money' => $payback->money,
			'bank_card_id' => $payback->bank_card_id,
			'bank_name' => $payback->bank_name,
			'bank_des' => $payback->bank_name_des,
			'des' => $payback->des,
			'list' => []
		];
		$jiekuan = JieKuan::find()->where("apply_id in ({$payback->jie_kuan_ids})")->all();
		foreach ($jiekuan as $v) {
			$data['list'][] = [
				'money' => $v->money,
				'time' => date('Y-m-d H:i', $v->get_money_time),
				'des' => $v->des
			];
		}
		return data;
	}
	
	/**
	 * 付款详情
	 * @param \app\models\Apply $apply
	 * @return array
	 */
	protected function getPay($apply)
	{
		$data = [
			'to_name' => $apply->applyPay->to_name,
			'bank_card_id' => $apply->applyPay->bank_card_id,
			'bank_name' => $apply->applyPay->bank_name,
			'pay_type' => $apply->applyPay->pay_type,
			'des' => $apply->applyPay->des,
			'files' => json_decode($apply->applyBuy->files)
		];
		return $data;
	}
	
	/**
	 * 请购详情
	 * @param \app\models\Apply $apply
	 * @return array
	 */
	protected function getBuy($apply)
	{
		$data = [
				'to_name' => $apply->applyBuy->to_name,
				'bank_card_id' => $apply->applyBuy->bank_card_id,
				'bank_name' => $apply->applyBuy->bank_name,
				'des' => $apply->applyBuy->des,
				'files' => json_decode($apply->applyBuy->files),
				'buy_list' => BaseApplyLogic::instance()->getApplyBuyList($apply->apply_id)
		];
		return $data;
	}
	
	/**
	 * 需求单详情
	 * @param \app\models\Apply $apply
	 * @return array
	 */
	protected function getDemand($apply)
	{
		$data = [
				'des' => $apply->applyDemand->des,
				'files' => json_decode($apply->applyDemand->files),
				'demand_list' => BaseApplyLogic::instance()->getApplyDemandList($apply->apply_id),
		];
		return $data;
	}
	
	/**
	 * 用章详情
	 * @param \app\models\Apply $apply
	 * @return array
	 */
	protected function getUseChapter($apply)
	{
		$data = [
				'des' => $apply->applyUseChapter->des,
				'files' => json_decode($apply->applyUseChapter->files),
		];
		return $data;
	}
	
	/**
	 * 转正详情
	 * @param \app\models\Apply $apply
	 * @return array
	 */
	protected function getPositive($apply)
	{
		$positive = $apply->applyPositive;
		$data = [
				'prosecution' => $positive->prosecution,//自诉
				'summary' => $positive->summary,//总结
				'suggest' => $positive->suggest,//建议
				'org' => $positive->org,//试用期部门
				'job' => $positive->profession,//试用期职位
				'entry_time' => date('Y年m月d日',strtotime($positive->entry_time)),//入职时间
				'files' => json_decode($positive->files),
		];
		return $data;
	}
	
	/**
	 * 调职详情
	 * @param \app\models\Apply $apply
	 * @return array
	 */
	protected function getTransfer($apply)
	{
		$transfer = $apply->applyTransfer;
		$data = [
				'entry_time' => date('Y年m月d日',strtotime($transfer->entry_time)),
				'old_org' => $transfer->old_org_name,
				'old_profession' => $transfer->old_profession,
				'target_org' => $transfer->target_org_name,
				'target_profession' => $transfer->target_profession,
				'transfer_time' => date('Y年m月d日',strtotime($transfer->transfer_time)),
				'des' => $transfer->des,
				'files' => json_decode($transfer->files),
		];
		return $data;
	}
}