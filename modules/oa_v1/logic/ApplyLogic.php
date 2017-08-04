<?php

namespace app\modules\oa_v1\logic;


use app\models as appmodel;
use app\modules\oa_v1\models\ApplyBuyForm;
use app\modules\oa_v1\models\ApplyPayForm;
use app\modules\oa_v1\models\BackForm;
use app\modules\oa_v1\models\BaoxiaoForm;
use app\modules\oa_v1\models\BaseForm;
use app\modules\oa_v1\models\LoanForm;
use yii\data\Pagination;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * 申请单逻辑
 * @author Administrator
 *
 */
class ApplyLogic extends BaseLogic
{
	/**
	 * 获取申请列表
	 * @param array $search
	 * @param array $user
     *
     * @return array
	 */
	public function getApplyList($search,$user)
	{
		$type = $search['type'];
		$page = isset($search['page']) ? (1 <= $search['page'] ? (int)$search['page'] : 1) : 1;
		$page_size = @$search['page_size'] ? : 20;
		$keywords = iconv(mb_detect_encoding(@$search['keywords'],"UTF-8,GB2312,GBK"),"UTF-8//IGNORE",@$search['keywords']);
		$keywords = trim($keywords);
		if(isset($search['at']) && $search['at'] != '') {
            $apply_type = (array)@$search['at'];
        } else {
            $apply_type = null;
        }
		
		$query;

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
			->where(['approval_person_id' => $user['person_id']])
			-> andWhere(['>', 'result', 0])
			-> joinWith('apply a',true,'RIGHT JOIN')
			-> orderBy('create_time');
		}elseif(3 == $type){//我发起的
			$apply_model = new appmodel\Apply();
			$query = $apply_model::find()
			-> alias('a')
			-> where(['person_id'=>$user['person_id']])
			-> orderBy('create_time');
		}elseif(4 == $type){//抄送给我的
			$copy_model = new appmodel\ApplyCopyPerson();
			$query = $copy_model::find()-> joinWith('apply a',true,'RIGHT JOIN')-> where([
			    'copy_person_id'=>$user['person_id']
            ])->andWhere([
                'in', 'a.status', [4 , 5, 99]
            ])-> orderBy('create_time');
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
		if(isset($search['status']) && $search['status']){
			
			/*
			switch($search['status']){
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
			*/
			$arr_status = [];
			foreach($search['status'] as $v){
				switch($v){
					case 1://审核中
						array_push($arr_status ,1,11);
						break;
					case 2://财务确认中
						array_push($arr_status ,4);
						break;
					case 3://撤销
						array_push($arr_status ,3);
						break;
					case 4://审核不通过
						array_push($arr_status ,2);
						break;
					case 5://完成
						array_push($arr_status ,99);
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
		$approval = $model::find() -> where(['apply_id' => $apply_id]) -> orderBy('steep') -> asArray() -> all();
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
    
    /**
     * 待我审批
     *
     * @param $personId
     *
     * @return int|string
     */
	public function getToMe($personId)
    {
        return appmodel\ApprovalLog::find()->alias('a')->rightJoin('oa_apply b', 'b.apply_id = a.apply_id')->where([
            'approval_person_id' => $personId,
            'is_to_me_now' => 1
        ])->andWhere([
            'in', 'b.status', [1, 11]
        ])->count() ? : 0;
    }
    
    /**
     * 我已审核
     *
     * @param $personId
     *
     * @return int|string
     */
    public function getApprovalLogCount($personId)
    {
        return appmodel\ApprovalLog::find()->alias('a')->rightJoin('oa_apply b', 'b.apply_id = a.apply_id')->where([
            'a.approval_person_id' => $personId,
        ])->andWhere([
            '>', 'a.result', 0
        ])->count()  ? : 0;
    }
    
    /**
     * 我发起的
     *
     * @param $personId
     *
     * @return int|string
     */
    public function getApplyCount($personId)
    {
        return appmodel\Apply::find()->where([
            'person_id' => $personId,
        ])->count()  ? : 0;
    }
    
    /**
     * 抄送给我的
     *
     * @param $personId
     *
     * @return int|string
     */
    public function getCopyCount($personId)
    {
        return appmodel\ApplyCopyPerson::find()->alias('a')->rightJoin('oa_apply b', 'a.apply_id = b.apply_id')->where([
            'a.copy_person_id' => $personId,
        ])->andWhere([
            'in', 'b.status', [4 , 5, 99]
        ])->count() ? : 0;
    }
    
    /**
     * 补传附件
     *
     * @param appmodel\Apply $apply
     * @param $files
     *
     * @return boolean
     */
    public function addFiles($apply, $files)
    {
        switch ($apply->type) {
            case 2:
                $info = $apply->loan;
                break;
            case 4:
                $info = $apply->applyPay;
                break;
            case 5:
                $info = $apply->applyBuy;
                break;
            case 6:
                $info = $apply->applyDemand;
                break;
            case 7:
                $info = $apply->applyUseChapter;
                break;
            case 8:
                $info = $apply->assetGet;
                break;
            case 9:
                $info = $apply->assetBack;
                break;
            case 10:
                $info = $apply->applyPositive;
                break;
            case 11:
                $info = $apply->applyLeave;
                break;
            case 12:
                $info = $apply->applyTransfer;
                break;
            case 13:
                $info = $apply->applyOpen;
                break;
            default:
                $info = $apply->expense;
                break;
        }
        if($apply->type == 2) {
            $info->pics = ArrayHelper::merge(json_decode($info->pics), $files);
        } else {
            $info->files = ArrayHelper::merge(json_decode($info->files), $files);
        }
        if ($info->save()) {
            return true;
        }
        return false;
    }
    
    /**
     * 付款失败，重新下单
     *
     * @param appmodel\Person $person
     *
     * @return boolean
     */
    public function PayReApply($person)
    {
        $applyId = \Yii::$app->request->post('apply_id');
        $apply = appmodel\Apply::findOne($applyId);
        if (empty($apply) || $apply->status != 6 || !in_array($apply->type, [1, 2, 3, 4, 5])) {
            $this->error = '申请单不存在，或者该申请单不能重新申请';
            return false;
        }
        $bank_card_id = \Yii::$app->request->post('bank_card_id');
        $bank_name = \Yii::$app->request->post('bank_name');
        if($apply->type == 4 || $apply->type == 5) {
            $to_name = \Yii::$app->request->post('to_name');
            if (!$bank_card_id || !$bank_name || !$to_name) {
                $this->error = '参数错误';
                return false;
            }
        } else {
            if (!$bank_card_id || !$bank_name) {
                $this->error = '参数错误';
                return false;
            }
        }
        if ($apply &&  $apply->person_id != $person->person_id){
            $this->error = '错误操作';
            return false;
        }
        
        if($apply->type == 1) {
            return $this->reExpense($apply, $bank_name, $bank_card_id);
        } elseif($apply->type == 2) {
            return $this->reLoan($apply, $bank_name, $bank_card_id);
        } elseif($apply->type == 3) {
            return $this->rePayBack($apply);
        } elseif($apply->type == 4) {
            return $this->reApplyPay($apply, $bank_name, $bank_card_id, $to_name);
        } else {
            return $this->reApplyBuy($apply, $bank_name, $bank_card_id, $to_name);
        }
    }
    
    /**
     * 付款失败，报销重新申请
     *
     * @param appmodel\Apply $reApply
     * @param $bankName
     * @param $bankCardId
     *
     * @return boolean | integer
     * @throws Exception
     */
    public function reExpense($reApply, $bankName, $bankCardId)
    {
        $applyId = $this->getApplyId($reApply->type);
        $apply = new appmodel\Apply();
        $apply->apply_id = $applyId;
        $apply->title = $reApply->title;
        $apply->create_time = $_SERVER['REQUEST_TIME'];
        $apply->type = $reApply->type;
        $apply->person_id = $reApply->person_id;
        $apply->person = $reApply->person;
        $apply->status = 4;
        $apply->next_des = '财务付款';
        $apply->approval_persons = $reApply->approval_persons;
        $apply->copy_person = $reApply->copy_person;
        $apply->apply_list_pdf = $reApply->apply_list_pdf;
        $apply->cai_wu_need = $reApply->cai_wu_need;
        $apply->org_id = $reApply->org_id;
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();
        try{
            if (!$apply->save()) {
                throw new Exception('付款申请单创建失败');
            }
            /**
             * @var appmodel\BaoXiao $expense
             */
            $expense = $reApply->expense;
            $listIds = [];
            /**
             * @var appmodel\BaoXiaoList $v
             */
            foreach ($expense->list as $v) {
                $baoXiaoList = new appmodel\BaoXiaoList();
                $baoXiaoList->apply_id = $apply->apply_id;
                $baoXiaoList->des = $v->des;
                $baoXiaoList->money = $v->money;
                if (!$baoXiaoList->save()) {
                    throw new Exception('请购明细保存失败');
                }
                $listIds = [$baoXiaoList->id];
            }
            $baoXiao =  new appmodel\BaoXiao();
            $baoXiao->apply_id = $apply->apply_id;
            $baoXiao->bank_card_id = $bankCardId;
            $baoXiao->bank_name = $bankName;
            $baoXiao->money = $expense->money;
            $baoXiao->files = $expense->files;
            $baoXiao->bao_xiao_list_ids = implode(',', $listIds);
            if (!$baoXiao->save()) {
                throw new Exception('付款申请创建失败');
            }
            $this->approvalPerson($apply, $reApply->apply_id);
            $this->copyPerson($apply, $reApply->apply_id);
            // 已经重新申请
            $reApply->status = 7;
            if (!$reApply->save()) {
                throw new Exception('重新申请失败');
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        return $apply->apply_id;
    }
    
    /**
     * 付款失败，备用金重新申请
     *
     * @param appmodel\Apply $reApply
     * @param $bankName
     * @param $bankCardId
     *
     * @return boolean
     * @throws Exception
     */
    public function reLoan($reApply, $bankName, $bankCardId)
    {
        $applyId = $this->getApplyId($reApply->type);
        $apply = new appmodel\Apply();
        $apply->apply_id = $applyId;
        $apply->title = $reApply->title;
        $apply->create_time = $_SERVER['REQUEST_TIME'];
        $apply->type = $reApply->type;
        $apply->person_id = $reApply->person_id;
        $apply->person = $reApply->person;
        $apply->status = 4;
        $apply->next_des = '财务付款';
        $apply->approval_persons = $reApply->approval_persons;
        $apply->copy_person = $reApply->copy_person;
        $apply->apply_list_pdf = $reApply->apply_list_pdf;
        $apply->cai_wu_need = $reApply->cai_wu_need;
        $apply->org_id = $reApply->org_id;
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();
        try{
            if (!$apply->save()) {
                throw new Exception('申请失败',$apply->errors);
            }
            $this->approvalPerson($apply, $reApply->apply_id);
            $this->copyPerson($apply, $reApply->apply_id);
            /**
             * @var appmodel\JieKuan $loan
             */
            $loan = $reApply->loan;
            $model = new appmodel\JieKuan();
            $model->apply_id = $apply->apply_id;
            $model->bank_name = $bankName;
            $model->bank_card_id = $bankCardId;
            $model->bank_name_des = $loan->bank_name_des ? : '';
            $model->pics = $loan->pics;
            $model->money = $loan->money;
            $model->des = $loan->des;
            $model->tips = $loan->tips;
            $model->get_money_time = 0;
            $model->pay_back_time = 0;
            $model->is_pay_back = 0;
            $model->status = 1;
            if (!$model->save()) {
                throw new Exception('备用金保存失败', $model->errors);
            }
            // 已经重新申请
            $reApply->status = 7;
            if (!$reApply->save()) {
                throw new Exception('重新申请失败');
            }
            $transaction->commit();
        } catch (Exception $exception){
            $transaction->rollBack();
            throw $exception;
        }
        return $apply->apply_id;
    }
    
    /**
     * 收款失败，备用金归还重新申请
     *
     * @param $apply
     *
     * @return boolean
     */
    public function rePayBack($apply)
    {
        return true;
    }
    
    /**
     * 付款失败，付款单重新申请
     *
     * @param appmodel\Apply $reApply
     * @param $bankName
     * @param $bankCardId
     * @param $toName
     *
     * @return boolean
     * @throws Exception
     */
    public function reApplyPay($reApply, $bankName, $bankCardId, $toName)
    {
        $applyId = $this->getApplyId($reApply->type);
        $apply = new appmodel\Apply();
        $apply->apply_id = $applyId;
        $apply->title = $reApply->title;
        $apply->create_time = $_SERVER['REQUEST_TIME'];
        $apply->type = $reApply->type;
        $apply->person_id = $reApply->person_id;
        $apply->person = $reApply->person;
        $apply->status = 4;
        $apply->next_des = '财务付款';
        $apply->approval_persons = $reApply->approval_persons;
        $apply->copy_person = $reApply->copy_person;
        $apply->apply_list_pdf = $reApply->apply_list_pdf;
        $apply->cai_wu_need = $reApply->cai_wu_need;
        $apply->org_id = $reApply->org_id;
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!$apply->save()) {
                throw new Exception('付款申请单创建失败');
            }
            /**
             * @var appmodel\ApplyPay $pay
             */
            $pay = $reApply->applyPay;
            $applyPay =  new appmodel\ApplyPay();
            $applyPay->apply_id = $apply->apply_id;
            $applyPay->bank_card_id = $bankCardId;
            $applyPay->bank_name = $bankName;
            $applyPay->money = $pay->money;
            $applyPay->created_at = time();
            $applyPay->files = $pay->files;
            $applyPay->des = $pay->des;
            //$applyPay->pay_type = $pay->pay_type;
            $applyPay->to_name = $toName;
            if (!$applyPay->save()) {
                throw new Exception('付款申请创建失败');
            }
            $this->approvalPerson($apply, $reApply->apply_id);
            $this->copyPerson($apply, $reApply->apply_id);
            // 已经重新申请
            $reApply->status = 7;
            if (!$reApply->save()) {
                throw new Exception('重新申请失败');
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        return $apply->apply_id;
    }
    
    /**
     * 付款失败，请购单重新申请
     *
     * @param  appmodel\Apply $reApply
     * @param $bankName
     * @param $bankCardId
     * @param $toName
     *
     * @return boolean | integer
     * @throws Exception
     */
    public function reApplyBuy($reApply, $bankName, $bankCardId, $toName)
    {
        $applyId = $this->getApplyId($reApply->type);
        $apply = new appmodel\Apply();
        $apply->apply_id = $applyId;
        $apply->title = $reApply->title;
        $apply->create_time = $_SERVER['REQUEST_TIME'];
        $apply->type = $reApply->type;
        $apply->person_id = $reApply->person_id;
        $apply->person = $reApply->person;
        $apply->status = 4;
        $apply->next_des = '财务付款';
        $apply->approval_persons = $reApply->approval_persons;
        $apply->copy_person = $reApply->copy_person;
        $apply->apply_list_pdf = $reApply->apply_list_pdf;
        $apply->cai_wu_need = $reApply->cai_wu_need;
        $apply->org_id = $reApply->org_id;
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!$apply->save()) {
                throw new Exception('付款申请单创建失败');
            }
            /**
             * @var appmodel\ApplyBuy $buy
             */
            $buy = $reApply->applyBuy;
            $applyPay =  new appmodel\ApplyBuy();
            $applyPay->apply_id = $apply->apply_id;
            $applyPay->bank_card_id = $bankCardId;
            $applyPay->bank_name = $bankName;
            $applyPay->money = $buy->money;
            $applyPay->files = $buy->files;
            $applyPay->des = $buy->des;
            $applyPay->to_name = $toName;
            if (!$applyPay->save()) {
                throw new Exception('付款申请创建失败');
            }
            /**
             * @var appmodel\ApplyBuyList $v
             */
            foreach ($buy->buyList as $v) {
                $buyList = new appmodel\ApplyBuyList();
                $buyList->apply_id = $apply->apply_id;
                $buyList->asset_type_id = $v->asset_type_id;
                $buyList->asset_type_name = $v->asset_type_name;
                $buyList->asset_brand_id = $v->asset_brand_id;
                $buyList->asset_brand_name = $v->asset_brand_name;
                $buyList->name = $v->name;
                $buyList->price = $v->price;
                $buyList->amount = $v->amount;
                if ($buyList->save()) {
                    throw new Exception('请购明细保存失败');
                }
            }
            $this->approvalPerson($apply, $reApply->apply_id);
            $this->copyPerson($apply, $reApply->apply_id);
            $transaction->commit();
            // 已经重新申请
            $reApply->status = 7;
            if (!$reApply->save()) {
                throw new Exception('重新申请失败');
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        return $apply->apply_id;
    }
    
    /**
     * 审批人
     *
     * @param appmodel\Apply $apply
     * @param int $oldApplyId
     *
     * @return boolean
     * @throws Exception
     */
    public function approvalPerson($apply, $oldApplyId)
    {
        $approvalLogs = appmodel\ApprovalLog::find()->where(['apply_id' => $oldApplyId])->all();
        /**
         * @var appmodel\ApprovalLog $v
         */
        foreach ($approvalLogs as  $v) {
            $approval = new appmodel\ApprovalLog();
            $approval->apply_id = $apply->apply_id;
            $approval->approval_person_id = $v->approval_person_id;
            $approval->approval_person = $v->approval_person;
            $approval->steep = $v->steep;
            $approval->is_end = $v->is_end;
            $approval->is_to_me_now = $v->is_to_me_now;
            $approval->des = '付款失败，重新申请';
            $approval->result = $v->result;
            $approval->approval_time = time();
            if (!$approval->save()) {
                throw new Exception('审批人保存失败');
            }
        }
        return true;
    }
    
    /**
     * 抄送人
     * @param appmodel\Apply $apply
     * @param $oldApplyId
     *
     * @return bool
     * @throws Exception
     */
    public function copyPerson($apply, $oldApplyId)
    {
        $ApplyCopyPerson = appmodel\ApplyCopyPerson::find()->where(['apply_id' => $oldApplyId])->all();
        /**
         * @var appmodel\ApplyCopyPerson $v
         */
        foreach ($ApplyCopyPerson as  $v) {
            $copyPerson = new appmodel\ApplyCopyPerson();
            $copyPerson->apply_id = $apply->apply_id;
            $copyPerson->copy_person = $v->copy_person;
            $copyPerson->copy_person_id = $v->copy_person_id;
            if (!$copyPerson->save()) {
                throw new Exception('审批人保存失败');
            }
        }
        return true;
    }
    
    /**
     * @param $type
     *
     * @return string
     */
    public function getApplyId($type)
    {
        $form = new BaseForm();
        return $form->createApplyId($type);
    }
}