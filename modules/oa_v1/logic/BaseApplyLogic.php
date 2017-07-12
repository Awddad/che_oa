<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/13
 * Time: 15:22
 */

namespace app\modules\oa_v1\logic;


use app\logic\Logic;
use app\models\Apply;
use app\models\ApplyBuyList;
use app\models\ApplyDemandList;
use app\models\ApprovalLog;
use app\models\AssetGetList;
use app\models\CaiWuFuKuan;
use app\models\CaiWuShouKuan;
use app\models\Person;


/**
 * 申请基础类
 *
 * Class BaseApplyLogic
 * @package app\modules\oa_v1\controllers
 */
class BaseApplyLogic extends Logic
{
    /**
     * 审批流程
     *
     * @param Apply $apply
     * @return array
     */
    public function getFlowData($apply)
    {
        $data = [];
        $approvalLog = ApprovalLog::find()->where(['apply_id' => $apply->apply_id])->all();
        $data[] = [
            "title" => "发起申请",
            "name" => $apply->person,
            "date"=> date('Y-m-d H:i', $apply->create_time),
            "org" => PersonLogic::instance()->getOrgNameByPersonId($apply->person_id),
            "status" => 2
        ];
        
        if(!empty($approvalLog)) {
            $count = count($approvalLog);
            $perTime = $apply->create_time;
            foreach ($approvalLog as $k => $v){
                $status = $diff_time = 0;
                $title = $v->approval_person.'审批';
                if($v->is_to_me_now == 1 && $v->result == 0) {
                    $status = 1;
                    $diff_time = time() - $apply->create_time;
                    $title .= '中';
                }
                if($v->result == 1) {
                    $status = 2;
                    $diff_time = $v->approval_time - $perTime;
                    $perTime = $v->approval_time ;
                }
                if($v->result == 2) {
                    $status = 3;
                    $diff_time = $v->approval_time - $perTime;
                    $title .= '不通过';
                    $perTime = $v->approval_time ;
                }
                
                $data[] = [
                    "title" => $title,
                    "name" => $v->approval_person,
                    "date"=> $v->approval_time ? date('Y-m-d H:i', $v->approval_time) : '',
                    "org" => PersonLogic::instance()->getOrgNameByPersonId($v->approval_person_id),
                    "status" => $status,
                    'diff_time' => $diff_time,
                     'des' => $v->des,
                ];
                if ($count == $k + 1 && $apply->status == 99 && $apply->cai_wu_need == 1) {
                    $data[] = [
                        "title" => "完成",
                        "name" => '',
                        "date"=> date('Y-m-d H:i', $v->approval_time),
                        "org" => '',
                        "status" => 2,
                        'diff_time' => $v->approval_time - $apply->create_time
                    ];
                }
            }
        }
        if($apply->cai_wu_need == 2 && $apply->status == 4) {
            $data[] = [
                "title" => "财务确认",
                "name" => '',
                "date"=> '',
                "org" => '',
                'diff_time' => time() - $perTime,
                "status" => 1
            ];
        }
    
        if($apply->cai_wu_need == 2 && in_array($apply->status, [1, 2, 3, 11]) ) {
            $data[] = [
                "title" => "财务确认",
                "name" => '',
                "date"=> '',
                "org" => '',
                "status" => 0
            ];
        }
    
        if($apply->status == 99) {
            if ($apply->cai_wu_need == 2) {
                if ($apply->type == 3) {
                    $caiWuShouKuan = CaiWuShouKuan::findOne($apply->apply_id);
                    $data[] = [
                        "title" => "财务确认",
                        "name" => $apply->cai_wu_person,
                        "date"=> date('Y-m-d H:i', $caiWuShouKuan->shou_kuan_time),
                        "org" => PersonLogic::instance()->getOrgNameByPersonId($apply->cai_wu_person_id),
                        "status" => 2,
                        'diff_time' => $caiWuShouKuan->shou_kuan_time - $perTime
                    ];
                    $data[] = [
                        "title" => "完成",
                        "name" => '',
                        "date"=> date('Y-m-d H:i', $caiWuShouKuan->shou_kuan_time),
                        "org" => '',
                        "status" => 2,
                        'diff_time' => $caiWuShouKuan->shou_kuan_time - $apply->create_time
                    ];
                } else {
                    $caiWuFuKuan = CaiWuFuKuan::findOne($apply->apply_id);
                    $data[] = [
                        "title" => "财务确认",
                        "name" => $apply->cai_wu_person,
                        "date"=> date('Y-m-d H:i', $caiWuFuKuan->create_time),
                        "org" => PersonLogic::instance()->getOrgNameByPersonId($apply->cai_wu_person_id),
                        "status" => 2,
                        'diff_time' => $caiWuFuKuan->create_time - $perTime
                    ];
                    $data[] = [
                        "title" => "完成",
                        "name" => '',
                        "date"=> date('Y-m-d H:i', $caiWuFuKuan->create_time),
                        "org" => '',
                        "status" => 2,
                        'diff_time' => $caiWuFuKuan->create_time - $apply->create_time
                    ];
    
                }
            }
            
        }  else {
            $data[] = [
                "title" => "完成",
                "name" => '',
                "date"=> '',
                "org" => '',
                "status" => 0
            ];
        }
        
        return $data;
    }
    
    /**
     * 请购单列表
     *
     * @param $applyId
     * @return array
     */
    public function getApplyBuyList($applyId)
    {
        $data = [];
        $assetLogic = AssetLogic::instance();
        $list = ApplyBuyList::find()->where(['apply_id' => $applyId])->all();
        /**
         * @var ApplyBuyList $v
         */
        foreach ($list as $v) {
            $data[] = [
                'asset_type_name' => $assetLogic->getAssetType($v->asset_type_id),
                'asset_brand_name' => $assetLogic->getAssetBrand($v->asset_brand_id),
                'name' => $v->name,
                'price' => $v->price,
                'amount' => $v->amount,
                'in_amount' => $v->in_amount,
            ];
        }
        return $data;
    }
    
    /**
     * 需求单 需求明细
     * @param $applyId
     * @return array
     */
    public function getApplyDemandList($applyId)
    {
        $data = [];
        $list = ApplyDemandList::find()->where(['apply_id' => $applyId])->all();
        foreach ($list as $v) {
            $data[] = [
                'name' => $v->name,
                'amount' => $v->amount,
            ];
        }
        return $data;
    }
    
    /**
     * 获取请购基础信息
     * @param Apply $apply
     * @return array
     */
    public function getBaseApply($apply)
    {
        return [
            "apply_id" => $apply->apply_id,
            "create_time" => date('Y-m-d H:i', $apply->create_time),
            "next_des" => $apply->next_des,
            "title" => $apply->title,
            "type" => $apply->type,
            "person" => $apply->person,
            'date' => date('Y年m月d日', $apply->create_time),
            'copy_person' => $apply->copy_person,
            'approval_persons' => $apply->approval_persons ? : '--',
        	'pdf' => $apply->apply_list_pdf,
            'org' => Person::findOne($apply->person_id)->org_full_name,
            'status' => $apply->status
        ];
    }
    
    /**
     * 固定资产申请列表
     *
     * @param $applyId
     * @return array
     */
    public function getAssetGetList($applyId)
    {
        $data = [];
        $list = AssetGetList::find()->where(['apply_id' => $applyId])->all();
        $assetLogic = AssetLogic::instance();
        /**
         * @var AssetGetList $v
         */
        foreach ($list as $v) {
            $data[] = [
                'asset_type' => $assetLogic->getAssetType($v->asset->asset_type_id),
                'asset_brand' => $assetLogic->getAssetBrand($v->asset->asset_brand_id),
                'name' => $v->asset->name,
                'price' => $v->asset->price,
            ];
        }
        return $data;
    }
    
    /**
     * 固定资产归还列表
     * @param $assetListIds
     * @return array
     */
    public function getAssetBackList($assetListIds)
    {
        $data = [];
        $list = AssetGetList::find()->where(['in', 'id', explode(',', $assetListIds)])->all();
        $assetLogic = AssetLogic::instance();
        /**
         * @var AssetGetList $v
         */
        foreach ($list as $v) {
            $data[] = [
                'asset_type' => $assetLogic->getAssetType($v->asset->asset_type_id),
                'asset_brand' => $assetLogic->getAssetBrand($v->asset->asset_brand_id),
                'name' => $v->asset->name,
                'price' => $v->asset->price,
                'stock_number' => $v->assetList->stock_number
            ];
        }
        return $data;
    }
}