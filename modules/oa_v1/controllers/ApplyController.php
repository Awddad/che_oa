<?php
namespace app\modules\oa_v1\controllers;

use app\models\Apply;
use Yii;
use app\models as appmodel;
use app\modules\oa_v1\logic\ApplyLogic;
use app\modules\oa_v1\logic\PersonLogic;

class ApplyController extends BaseController
{
	
	public function actionGetList()
	{
		$get = Yii::$app -> request -> get();
		$logic = new ApplyLogic();
		$res = $logic -> getApplyList($get,$this -> arrPersonInfo);
		if(!$res){
			return $this -> _return(null,403);
		}
		$data = ['page'=>$res['pages'],'res'=>[]];
		foreach($res['data'] as $v){
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
		return $this -> _return($data,200);
		
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
		$model = new ApplyLogic();
		$apply = $model -> getApplyInfo($apply_id,1);
		if(!$apply){
			return $this -> _return('报销单不存在！',403);
		}
		$data = $this -> getData($apply);
		$data['info'] = [
				'money' => $apply['info']['money'],
				'bank_card_id' => $apply['info']['bank_card_id'],
				'bank_name' => $apply['info']['bank_name'],
				'bank_des' => $apply['info']['bank_name_des'],
				'file' => json_decode($apply['info']['files']),
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
		return $this -> _return($data,200);
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
		$model = new ApplyLogic();
		$apply = $model -> getApplyInfo($apply_id,2);
		if(!$apply){
			return $this -> _return('借款单不存在！',403);
		}
		$data = $this -> getData($apply);
		$data['info'] = [
			'money' => $apply['info']['money'],
			'bank_card_id' => $apply['info']['bank_card_id'],
			'bank_name' => $apply['info']['bank_name'],
			'bank_des' => $apply['info']['bank_name_des'],
			'tips' => $apply['info']['tips'],
			'des' => $apply['info']['des'],
			'pics' => explode(',',$apply['info']['pics']),
			'is_pay_back' => $apply['info']['is_pay_back'],
		];
		if($apply['caiwu']['fukuan']){
			$data['caiwu'] = $this -> getFukuanData($apply);
		}
		return $this -> _return($data,200);
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
		$model = new ApplyLogic();
		$apply = $model -> getApplyInfo($apply_id,3);
		if(!$apply){
			return $this -> _return('还款单不存在！',403);
		}
		$data = $this -> getData($apply);
		$data['info'] = [
			'money' =>  $apply['info']['money'],
			'bank_card_id' => $apply['info']['bank_card_id'],
			'bank_name' => $apply['info']['bank_name'],
			'bank_des' => $apply['info']['bank_name_des'],
			'des' => $apply['info']['des'],
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
		return $this -> _return($data,200);
	}
	
	
	
	
	
	protected function getData($apply)
	{
		$data = [
				'apply_id' => $apply['apply_id'],
				'create_time' => date('Y-m-d h:i:s',$apply['create_time']),
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
									'time' => $v['approval_time']? date('Y-m-d h:i:s',$v['approval_time']):'',
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
	
	
	/**
	 * 获取状态值
	 */
	public function actionGetType()
	{
		$res = \app\modules\oa_v1\logic\TreeTagLogic::instance()->getTreeTagsByParentId();
		return $this -> _return($res,200);
	}
	/**
	 * 获取银行卡
	 */
	public function actionGetBankcard()
	{
		$cards = appmodel\PersonBankInfo::find() -> where(['person_id' => $this -> arrPersonInfo['person_id']]) -> asArray() -> all();
		foreach($cards as $v){
			$data[] = ['card_id'=>$v['bank_card_id'],'bank_name'=>$v['bank_name'],'bank_des'=>$v['bank_name_des']];
		}
		return $this -> _return($data,200);
	}
	
	/**
	 * 添加银行卡
	 */
	public function actionAddBankcard()
	{
		$request = Yii::$app -> request;
		if($request -> isPost){
			$post = $request -> post();
			if($post['card_id'] && $post['bank_name'] && $post['bank_des']){
				return $this -> _return(null,200);
			}
			return $this -> _return(null,403);
		}	
		return $this -> _return(null,403);
	}
	/**
	 * 获取员工列表
	 */
	public function actionGetUserList()
	{
		$data = PersonLogic::instance() -> getSelectPerson();
		return $this -> _return($data,200);
	}

    /**
     * 申请撤销操作
     * @return array
     */
    public function actionRevoke()
    {
        $personId = Yii::$app->request->post('person_id');
        $applyId = Yii::$app->request->post('apply_id');

        $apply = Apply::findOne($applyId);
        if (!$apply) {
            return $this->_returnError(1010);
        }

        if ($apply->person_id != $personId) {
            return $this->_returnError(2101);
        }

        if (!in_array($apply->status, [Apply::STATUS_WAIT, Apply::STATUS_ING])) {
            return $this->_returnError(2001);
        }

        $apply->status = Apply::STATUS_REVOKED;
        $apply->next_des = '该申请已撤销';

        if ($apply->save()) {
            return $this->_return('', 200);
        } else {
            return $this->_returnError(404, $apply->errors);
        }
    }
}