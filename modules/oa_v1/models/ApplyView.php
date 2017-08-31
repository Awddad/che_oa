<?php
namespace app\modules\oa_v1\models;



use app\logic\CnyLogic;
use app\models\ApplyDemand;
use app\models\ApplyProjectRole;
use app\models\ApplyTravel;
use app\models\ApplyUseChapter;
use app\models\AssetBack;
use app\models\AssetGet;
use app\models\BaoXiao;
use app\models\BaoXiaoList;
use app\models\GoodsUp;
use app\models\TagTree;
use app\modules\oa_v1\logic\BaseApplyLogic;
use app\models\Apply;
use app\models\JieKuan;
use app\modules\oa_v1\logic\AssetLogic;
use app\modules\oa_v1\logic\JieKuanLogic;
use app\models\Employee;
use app\modules\oa_v1\logic\RegionLogic;
use app\models\Person;
use yii;

/**
 * 申请单详情
 *
 * Class ApplyView
 * @package app\modules\oa_v1\models
 */
class ApplyView extends BaseForm
{

    protected $typeMethod = [
        1 => 'Baoxiao',
        2 => 'Loan',
        3 => 'PayBack',
        4 => 'Pay',
        5 => 'Buy',
        6 => 'Demand',
        7 => 'UseChapter',
        8 => 'AssetGet',
        9 => 'AssetBack',
        10 => 'Positive',
        11 => 'Leave',
        12 => 'Transfer',
        13 => 'Open',
        14 => 'GoodsUp',
        15 => 'Travel',
        16 => 'ProjectRole',
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
        /**
         * @var BaoXiao $baoxiao
         */
		$baoxiao = $apply->expense;
		$data  = [
				'money' => $baoxiao->money,
				'bank_card_id' => $baoxiao->bank_card_id,
				'bank_name' => $baoxiao->bank_name,
				'bank_des' => $baoxiao->bank_name_des,
				'files' => json_decode($baoxiao->files),
				'list' => []
		];
        /**
         * @var BaoXiaoList $v
         */
        $total = 0;
		foreach ($baoxiao->list as $v) {
			$data['list'][] = [
					'money' => $v->money,
					//'type_name' => $v->type_name,
					//'type' => $v->type,
					'des' => $v->des
			];
            $total += $v['money'];
		}
        $data['total_supper'] = $total;
        $data['total'] = \Yii::$app->formatter->asCurrency($total);
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
			'files' => json_decode($loan->pics),
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
		return $data;
	}

	/**
	 * 付款详情
	 * @param \app\models\Apply $apply
	 * @return array
	 */
	protected function getPay($apply)
	{
	    //$tagTree = TagTree::findOne($apply->applyPay->pay_type);
		$data = [
			'to_name' => $apply->applyPay->to_name,
			'bank_card_id' => $apply->applyPay->bank_card_id,
			'bank_name' => $apply->applyPay->bank_name,
			'pay_type' => $apply->applyPay->pay_type,
			'des' => $apply->applyPay->des,
            'money' => $apply->applyPay->money,
            //'pay_type_name' => $tagTree->name,
			'files' => json_decode($apply->applyPay->files)
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
				'status' => $apply->applyBuy->status,
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
            'status' => $apply->applyDemand->status,
            'status_name' => ApplyDemand::STATUS[$apply->applyDemand->status],
            'buy_type' => ApplyDemand::BUY_TYPE[$apply->applyDemand->buy_type],
            'apply_buy_id' => $apply->applyDemand->apply_buy_id,
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
        if ($apply->applyUseChapter->use_type) {
            $use_type =  ApplyUseChapter::USE_TYPE[$apply->applyUseChapter->use_type];
        } else {
            $use_type = '--';
        }
	    
		$data = [
		    'use_type' => $use_type,
		    'chapter_type' => ApplyUseChapter::STATUS[$apply->applyUseChapter->chapter_type],
		    'name' => $apply->applyUseChapter->name,
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
				'positive_time' => date('Y年m月d日',strtotime($positive->positive_time)),//转正时间
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
		        'old_salary' => $transfer->old_base_salary,
		        'old_jixiao' => $transfer->old_jixiao,
				'target_org' => $transfer->target_org_name,
				'target_profession' => $transfer->target_profession,
		        'target_salary' => $transfer->target_base_salary,
		        'target_jixiao' => $transfer->target_jixiao,
				'transfer_time' => date('Y年m月d日',strtotime($transfer->transfer_time)),
				'des' => $transfer->des,
				'files' => json_decode($transfer->files),
		];
		return $data;
	}

	/**
	 * 离职详情
	 * @param \app\models\Apply $apply
	 * @return array
	 */
	protected function getLeave($apply)
	{
	   $leave = $apply->applyLeave;
	   $employee = Employee::find()->where(['person_id'=>$apply->person_id])->one();
	   $data = [
	       'leave_time'=>date('Y年m月d日',strtotime($leave->leave_time)),
	       'des' => $leave->des,
	       'profession' => Person::find()->where(['person_id'=>$apply->person_id])->one()->profession,
	       'stock_status' => $leave->stock_status ? '是' : '否',
	       'finance_status' => $leave->finance_status ? '是' : '否',
	       'account_status' => $leave->account_status ? '是' : '否',
	       'work_status' => $leave->work_status ? '是' : '否',
	       'handover_id' => $leave->handover_person_id,
	       'handover' => $leave->handover,
	       'files' => json_decode($leave->files),
	       'stock_list' => AssetLogic::instance()->getAssetHistory($apply->person_id),
	       'finance_list' => JieKuanLogic::instance()->getHistory($apply->person_id),
	       'qq' => isset($employee->account)?$employee->account->qq:'--',
	       'email' => isset($employee->account)?$employee->account->email:'--',
	       'tel' => isset($employee->account)?$employee->account->tel:'--',
	   ];
	   return $data;
	}

	/**
	 * 开店申请
	 * @param \app\models\Apply $apply
	 * @return array
	 */
	protected function getOpen($apply)
	{
	    $open = $apply->applyOpen;
	    $data = [
	        'address' => $open->address,
	        'rental' => $open->rental,
	        'summary' => $open->summary,
	        'city' => $open->district_name,
	        'files' => json_decode($open->files),
	    ];
	    return $data;
	}

    /**
     * 固定资产申请
     *
     * @param Apply $apply
     * @return array
     */
	public function getAssetGet($apply)
    {
        /**
         * @var AssetGet $applyBuy
         */
        $applyBuy= $apply->assetGet;
        $data = [
            'des' => $applyBuy->des,
            'files' => json_decode($applyBuy->files),
            'list' => BaseApplyLogic::instance()->getAssetGetList($apply->apply_id)
        ];
        return $data;
    }

    /**
     * 固定资产归还
     *
     * @param Apply $apply
     * @return array
     */
    public function getAssetBack($apply)
    {
        /**
         * @var AssetBack $assetBack\
         */
        $assetBack = $apply->assetBack;
        $data = [
            'des' => $assetBack->des,
            'files' => json_decode($assetBack->files),
            'list' => BaseApplyLogic::instance()->getAssetBackList($assetBack->asset_list_ids)
        ];
        return $data;
    }
    
    /**
     * @param Apply $apply
     *
     * @return array
     */
    public function getGoodsUp($apply)
    {
        /**
         * @var GoodsUp $goodsUp
         */
        $goodsUp = $apply->goodsUp;
        $data = [
            'des' => $goodsUp->des,
            'files' => json_decode($goodsUp->files)?:[],
            'list' => $goodsUp->goodsUpDetail
        ];
        return $data;
    }
    
    public function getTravel($apply)
    {
        /**
         * @var ApplyTravel $travel
         */
        $travel = $apply->travel;
        $data = [
            'des' => $travel->des,
            'files' => json_decode($travel->files)?:[],
            'list' => $travel->travelList
        ];
        return $data;
    }
    
    /**
     * @param Apply $apply
     *
     * @return array
     */
    public function getProjectRole($apply)
    {
        /**
         * @var ApplyProjectRole $projectRole
         */
        $projectRole = $apply->projectRole;
        $data = [
            'des' => $projectRole->des,
            'files' => json_decode($projectRole->files)?:[],
            'project_name' => $projectRole->project_name,
            'role_name' => $projectRole->role_name,
            'begin_at' => $projectRole->begin_at,
            'end_at' => $projectRole->end_at,
        ];
        return $data;
    }
}