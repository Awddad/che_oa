<?php

namespace app\modules\oa_v1\logic;


use app\models as appmodel;
use app\modules\oa_v1\models\ApplyBuyForm;
use app\modules\oa_v1\models\ApplyPayForm;
use app\modules\oa_v1\models\BackForm;
use app\modules\oa_v1\models\BaoxiaoForm;
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
            $info->files = ArrayHelper::merge(json_decode($info->pics), $files);
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
        if (empty($apply) || $apply->status == 6 || !in_array($apply->type, [1, 2, 3, 4, 5])) {
            $this->error = '申请单不存在，或者该申请单不能重新申请';
            return false;
        }
        if ($apply &&  $apply->person_id != $person->person_id){
            $this->error = '错误操作';
            return false;
        }
        $bank_card_id = \Yii::$app->request->post('bank_card_id');
        $bank_name = \Yii::$app->request->post('bank_name');
        if($apply->type == 4 || $apply->type == 5) {
            $to_name = \Yii::$app->request->post('bank_name');
            if (!$bank_card_id || $bank_name || $to_name) {
                $this->error = '参数错误';
                return false;
            }
        } else {
            if (!$bank_card_id || !$bank_name) {
                $this->error = '参数错误';
                return false;
            }
        }
        if($apply->type == 1) {
            return $this->reExpense($apply, $person);
        } elseif($apply->type == 2) {
            return $this->reLoan($apply, $person);
        } elseif($apply->type == 3) {
            return $this->rePayBack($apply, $person);
        } elseif($apply->type == 4) {
            return $this->reApplyPay($apply, $person);
        } else {
            return $this->reApplyBuy($apply, $person);
        }
    }
    
    /**
     * 付款失败，报销重新申请
     *
     * @param $apply
     * @param appmodel\Person $person
     *
     * @return boolean | integer
     */
    public function reExpense($apply, $person)
    {
        $model = new BaoxiaoForm();
        $data['BaoxiaoForm'] = [
            ''
        ];
        $model -> load($data);
        $model -> title = $model -> createApplyTitle($person->person_id);
        $model -> create_time = time();
        if ($model->validate() && $apply_id = $model -> saveBaoxiao()) {
            return $apply_id;
        }
        return false;
    }
    
    /**
     * 付款失败，备用金重新申请
     *
     * @param $apply
     * @param appmodel\Person $person
     *
     * @return boolean
     */
    public function reLoan($apply, $person)
    {
        $model = new LoanForm();
        $data['BaoxiaoForm'] = [
            ''
        ];
        if ( $model -> load($data) && $model->validate() && $apply_id = $model->save($person)) {
            return $apply_id;
        }
        return false;
    }
    
    /**
     * 收款失败，备用金归还重新申请
     *
     * @param $apply
     * @param appmodel\Person $person
     *
     * @return boolean
     */
    public function rePayBack($apply, $person)
    {
        $model = new BackForm();
        $data['BaoxiaoForm'] = [
            ''
        ];
        if ( $model -> load($data) && $model->validate() && $apply_id = $model->save($person)) {
            return $apply_id;
        }
        return false;
    }
    
    /**
     * 付款失败，付款单重新申请
     *
     * @param $apply
     * @param appmodel\Person $person
     *
     * @return boolean
     */
    public function reApplyPay($apply, $person)
    {
        $model = new ApplyPayForm();
        $data['BaoxiaoForm'] = [
            ''
        ];
        if ( $model -> load($data) && $model->validate() && $apply_id = $model->save($person)) {
            return $apply_id;
        }
        return false;
    }
    
    /**
     * 付款失败，请购单重新申请
     *
     * @param  appmodel\Apply $reApply
     * @param appmodel\Person $person
     *
     * @return boolean
     */
    public function reApplyBuy($reApply, $person)
    {
        $applyId = '0000';
        $pdfUrl = '';
        $nextName = PersonLogic::instance()->getPersonName($this->approval_persons[0]);
    
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
        $apply->apply_list_pdf = $pdfUrl;
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
            $applyPay->bank_card_id = $buy->bank_card_id;
            $applyPay->bank_name = $buy->bank_name;
            $applyPay->money = $buy->money;
            $applyPay->files = $buy->files;
            $applyPay->des = $buy->des;
            $applyPay->to_name = $buy->to_name;
            if (!$applyPay->save()) {
                throw new Exception('付款申请创建失败');
            }
//            foreach ($buy->buyList as $v) {
//
//            }
//            $this->approvalPerson($apply);
//            $this->copyPerson($apply);
//            $this->saveApplyBuyList();
//            $transaction->commit();
//            return $apply;
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
    
    /**
     * @param $apply
     *
     * @return array
     */
    public function getApprovalLog($apply)
    {
        $approval = appmodel\ApprovalLog::find()->select('approval_person_id')->where([
            'apply_id' => $apply->apply_id
        ])->asArray()->all();
        return ArrayHelper::getColumn($approval, 'approval_person_id');
    }
    
    /**
     * @param $apply
     *
     * @return array
     */
    public function getCopyPersonLog($apply)
    {
        $approval = appmodel\ApplyCopyPerson::find()->select('copy_person_id')->where([
            'apply_id' => $apply->apply_id
        ])->asArray()->all();
        return ArrayHelper::getColumn($approval, 'approval_person_id');
    }
}