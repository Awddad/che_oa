<?php

namespace app\modules\oa_v1\controllers;

use app\models\Apply;
use app\modules\oa_v1\logic\TreeTagLogic;
use Yii;
use app\models as appmodel;
use app\modules\oa_v1\logic\ApplyLogic;
use app\modules\oa_v1\logic\PersonLogic;
use yii\db\Exception;
use app\modules\oa_v1\logic\AssetLogic;
use app\modules\oa_v1\models\ApplyView;
use app\modules\oa_v1\logic\Profession;
use app\modules\oa_v1\logic\RegionLogic;
use app\models\PersonBankInfo;

class ApplyController extends BaseController
{
	protected $apply_status = [
		'发起申请',
		'完成'
	];
	protected $approval_status = [
		'%s审批',
		'%s审批',
		'%s审批不通过',
		'%s审批中'
	];
	protected $caiwu_status = [
		'财务确认',
		'财务确认中',
		'财务确认'
	];

	/**
	 * 列表
	 */
	public function actionGetList() {
		$get = Yii::$app->request->get();
		$logic = new ApplyLogic ();
		$res = $logic->getApplyList($get, $this->arrPersonInfo);
		if (!$res) {
			return $this->_return(null, 403);
		}
		$data = [
			'page' => $res ['pages'],
			'res' => []
		];
		foreach ($res ['data'] as $k=>$v) {
			$data ['res'] [] = [
				'id' => ($data['page']['currentPage']-1)*$data['page']['perPage'] + $k+1,
				'apply_id' => $v ['apply_id'], // 审批单编号
				'date' => date('Y-m-d H:i', $v ['create_time']), // 创建时间
				'type' => $v ['type'], // 类型
				'type_value' => $this->type [$v ['type']], // 类型值
				'title' => $v ['title'], // 标题
				'person' => $v ['person'], // 发起人
				'approval_persons' => str_replace(',', ' -> ', $v ['approval_persons']), // 审批人
				'copy_person' => $v ['copy_person'] ?: '--', // 抄送人
				'status' => $v ['status'], // 状态
				'next_des' => $v ['next_des'], // 下步说明
				'can_cancel' => in_array($v ['status'], [
					1,
					11
				]) ? 1 : 0
			]; // 是否可以撤销
		}
		return $this->_return($data, 200);
	}
    
    /**
     * 申请详情
     *
     * @return array
     */
	public function actionGetInfo() {
		
		$get = Yii::$app->request->get();
		if (!isset($get['apply_id']) || !$get['apply_id']) {
			return $this->_return("apply_id不能为空", 403);
		} else {
			$apply_id = trim($get['apply_id']);
		}
		if (!isset($get['type']) || !$get['type']) {
			return $this->_return("type不能为空", 403);
		} else {
			$type = (int)$get['type'];
		}
        /**
         * @var Apply $apply
         */
		$apply = Apply::find()->where(['apply_id'=>$apply_id,'type'=>$type])->one();
		if(empty($apply)){
			return $this->_return('申请单不存在！', 403);
		}
		$model = new ApplyView();
		$data = $model->getApply($apply);
		
		return $this->_return($data);
	}

	

	/**
	 * 获取状态值
	 */
	public function actionGetType() {
		$res = \app\modules\oa_v1\logic\TreeTagLogic::instance()->getTreeTagsByParentId(17);
		return $this->_return($res, 200);
	}

	/**
	 * 获取银行卡
	 */
	public function actionGetBankcard() {
		$cards = appmodel\PersonBankInfo::find()->where([
					'person_id' => $this->arrPersonInfo ['person_id']
				])->asArray()->all();
		$data = [];
		foreach ($cards as $v) {
			$data [] = [
				'card_id' => $v ['bank_card_id'],
				'id' => intval($v ['id']),
				'bank_name' => $v ['bank_name'],
				'bank_des' => $v ['bank_name_des']
			];
		}
		return $this->_return($data, 200);
	}

	/**
	 * 添加银行卡
	 */
	public function actionAddBankcard() {
		$request = Yii::$app->request;
		if ($request->isPost) {
			$post = $request->post();
			if ($post ['card_id'] && $post ['bank_name']) {
				/* $obj = new \app\logic\server\QuanXianServer ();
				$intPersonId = $this->arrPersonInfo ['person_id'];
				$strBankName = $post ['bank_name'];
				$strBankNameDes = $post ['bank_des'];
				$strCardId = $post ['card_id'];
				$res = $obj->curlAddUserBankList($intPersonId, $strBankName, $strBankNameDes, $strCardId);
				return $this->_return(null, $res ? 200 : 404); */
			    $model = new PersonBankInfo();
			    $model->bank_card_id = $post ['card_id'];
			    $model->bank_name = $post ['bank_name'];
			    //$model->bank_name_des = $post ['bank_des'];
			    $model->person_id = $this->arrPersonInfo ['person_id'];
			    $model->is_salary = 0;
			    if($model->save()){
			        return $this->_return('成功');
			    }else{
			        return $this->_return(null,404);
			    }
			}
			return $this->_return(null, 403);
		}
		return $this->_return(null, 403);
	}

	/**
	 * 获取员工列表
	 */
	public function actionGetUserList() {
		$data = PersonLogic::instance()->getSelectPerson($this->arrPersonInfo,$this->companyIds);
		return $this->_return($data, 200);
	}

	/**
	 * 申请撤销操作
	 *
	 * @return array
	 */
	public function actionRevoke() {
		$personId = Yii::$app->request->post('person_id');
		$applyId = Yii::$app->request->post('apply_id');

		$apply = Apply::findOne($applyId);
		if (!$apply) {
			return $this->_returnError(1010);
		}

		if ($apply->person_id != $personId) {
			return $this->_returnError(2101);
		}

		if (!in_array($apply->status, [
					Apply::STATUS_WAIT,
					Apply::STATUS_ING
				])) {
			return $this->_returnError(2001);
		}

		$apply->status = Apply::STATUS_REVOKED;
		$apply->next_des = '该申请已撤销';
		// 还款单撤销特殊处理
		if($apply->type == 3) {
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            try {
                if (!$apply->save()) {
                    throw new Exception('撤销失败');
                }
                $payBack = appmodel\PayBack::findOne($applyId);
                $applyIds = explode(',', $payBack->jie_kuan_ids);
                //改变借款单状态
                foreach ($applyIds as $apply_id) {
                    appmodel\JieKuan::updateAll(['status' => 99], ['apply_id' => $apply_id]);
                }
                $transaction->commit();
                return $this->_return('', 200);
            } catch (Exception $e) {
                $transaction->rollBack();
                return $this->_returnError(404, $e);
            }
        } else {
		    if($apply->type == 8) {
		        AssetLogic::instance()->assetGetCancel($apply);
            }
            if($apply->type == 9) {
                AssetLogic::instance()->assetBackCancel($apply);
            }
            if ($apply->save()) {
                return $this->_return('', 200);
            } else {
                return $this->_returnError(404, $apply->errors);
            }
        }
	}
	/**
	 * 获取资产类别
	 */
	public function actionGetAssetType()
	{
		$parent_id = \yii::$app->request->get('pid',0);
		$res = AssetLogic::instance()->getAssetTypeByParentId($parent_id);
		return $this->_return($res);
	}
	/**
	 * 获得品牌
	 */
	public function actionGetBrand()
	{
		$res = AssetLogic::instance()->getAssetBrandList();
		return $this->_return($res);
	}
    
    /**
     * 获取收入支出类别
     * @param $id
     * @return array
     */
    public function actionTreeTag($id)
    {
        $data = TreeTagLogic::instance()->getTreeTagsByParentId($id);
        if (!$data) {
            return $this->_return($data, 400, '获取失败');
        }
        return $this->_return($data);
    }
	/**
	 * 获得职位列表
	 */
	public function actionGetProfession()
	{
	    $res = Profession::instance()->getList();
	    return $this->_return($res);
	}
	
	/**
	 * 获得地区列表
	 */
	public function actionGetRegion()
	{
	    $res = RegionLogic::instance()->getRegion();
	    return $this->_return($res);
	}
    
    /**
     * 删除银行卡
     */
    public function actionDeleteBankcard() {
        $request = Yii::$app->request;
        if ($request->isPost) {
            $post = $request->post();
            if ($post ['bankcard_id']) {
                $model = appmodel\PersonBankInfo::findOne($post['bankcard_id']);
                if($model && $model->delete()){
                    return $this->_return('成功');
                }else{
                    return $this->_return(null,404);
                }
            }
            return $this->_return(null, 403);
        }
        return $this->_return(null, 403);
    }
    
    /**
     * 财务驳回
     *
     */
    public function actionCaiwuRefuse()
    {
        $applyId = Yii::$app->request->post('apply_id');
        $reason = Yii::$app->request->post('reason');
        if(!$applyId || !$reason) {
            return $this->_returnError(403);
        }
        $apply = Apply::findOne($applyId);
        if(empty($apply)){
            return $this->_returnError(2408);
        }
        if($apply->cai_wu_need != 2){
            return $this->_returnError(2406);
        }
        if($apply->status != 4){
            return $this->_returnError(2406);
        }
        $apply->status = 5;
        $apply->cai_wu_person = $this->arrPersonInfo->person_name;
        $apply->cai_wu_time = time();
        $apply->cai_wu_person_id = $this->arrPersonInfo->person_id;
        $apply->caiwu_refuse_reason = $reason;
        if($apply->type == 3) {
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            try {
                if (!$apply->save()) {
                    throw new Exception('驳回失败');
                }
                $payBack = appmodel\PayBack::findOne($applyId);
                $applyIds = explode(',', $payBack->jie_kuan_ids);
                //改变借款单状态
                foreach ($applyIds as $apply_id) {
                    appmodel\JieKuan::updateAll(['status' => 99], ['apply_id' => $apply_id]);
                }
                $transaction->commit();
                return $this->_return('', 200);
            } catch (Exception $e) {
                $transaction->rollBack();
                return $this->_returnError(404, $e);
            }
        } else {
            if (!$apply->save()) {
                return $this->_returnError(2047);
            }
        }
        return $this->_return([], 200);
    }
}
