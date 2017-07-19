<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/29
 * Time: 13:28
 */

namespace app\modules\oa_v1\logic;


use app\logic\CnyLogic;
use app\logic\Logic;
use app\logic\MyTcPdf;
use app\models\Apply;
use app\models\ApplyBuyList;
use app\models\ApplyDemandList;
use app\models\ApplyUseChapter;
use app\models\AssetGetList;
use app\models\AssetList;
use app\models\BaoXiaoList;
use app\models\JieKuan;
use app\models\Person;
use app\models\TagTree;
use app\models\Employee;

class PdfLogic extends Logic
{
    /**
     * 报销单
     *
     * @param Apply $apply
     *
     * @return string
     */
    public function expensePdf($apply)
    {
        $myPdf = new MyTcPdf();
        $root_path = $this->getFilePath($apply);
        if(file_exists($root_path)){
            unlink($root_path);
        }
        $person = Person::findOne($apply->person_id);
        $arrInfo = [
            'apply_date' => date('Y年m月d日',$apply->create_time),
            'apply_id' => $apply -> apply_id,
            'org_full_name' => $person->org_full_name,
            'person' => $apply->person,
            'bank_name' => $apply->expense->bank_name.$apply->expense-> bank_name_des,
            'bank_card_id' => $apply->expense -> bank_card_id,
            'approval_person' =>$apply->approval_persons,//多个人、分隔
            'copy_person' => $apply->copy_person ? : '--',//多个人、分隔
            'list' => [],
            'tips' => '--',
            'caiwu' => $apply->cai_wu_person ? : ''
        ];
        $baoXiaoList = BaoXiaoList::find()->where(['apply_id' => $apply->apply_id])->all();
        $total = 0.00;
        foreach($baoXiaoList as $v){
            $arrInfo['list'][] = [
                'type_name' => $v['type_name'],
                'money' => \Yii::$app->formatter->asCurrency($v['money']),
                'detail' => @$v['des']
            ];
            $total += $v['money'];
        }
        $arrInfo['total'] = \Yii::$app->formatter->asCurrency($total);
        $arrInfo['total_supper'] = CnyLogic::instance()->cny($total);
        $myPdf->createdPdf($root_path, $arrInfo, 'baoxiao');
        
        return [
            'path' => $root_path,
            'name' => $apply->apply_id.'.pdf'
        ];
    }
    
    /**
     * 借款单
     *
     * @param Apply $apply
     * @return string
     */
    public function loanPdf($apply)
    {
        $pdf = new MyTcPdf();
        $root_path = $this->getFilePath($apply);
        if(file_exists($root_path)){
            unlink($root_path);
        }
        $person = Person::findOne($apply->person_id);
       
        $arrInfo =  [
            'apply_date' => date('Y年m月d日', $apply->create_time),
            'apply_id' => $apply->apply_id,
            'org_full_name' => $person->org_full_name,
            'person' => $person->person_name,
            'bank_name' => $apply->loan->bank_name,
            'bank_card_id' => $apply->loan->bank_card_id,
            'money' => \Yii::$app->formatter->asCurrency($apply->loan->money),
            'money_supper' => CnyLogic::instance()->cny($apply->loan->money),
            'detail' => $apply->loan->des,
            'tips' => $apply->loan->tips,
            'approval_person' =>$apply->approval_persons,//多个人、分隔
            'copy_person' => $apply->copy_person ?: '--',//多个人、分隔
            'caiwu' => $apply->cai_wu_person ? : ''
        ];
        
        $pdf->createdPdf($root_path, $arrInfo, 'loan');
        return [
            'path' => $root_path,
            'name' => $apply->apply_id.'.pdf'
        ];
    }
    
    /**
     * 还款单
     *
     * @param Apply $apply
     * @return string
     */
    public function payBackPdf($apply)
    {
        $pdf = new MyTcPdf();
        $root_path = $this->getFilePath($apply);
        if(file_exists($root_path)){
            unlink($root_path);
        }
        
        $person = Person::findOne($apply->person_id);
        $loanIds = explode(',', $apply->payBack->jie_kuan_ids);
        $data = [];
        $total = 0;
        foreach ($loanIds as $apply_id) {
            $back = JieKuan::findOne($apply_id);
            $data[] = [
                'create_time' => date('Y-m-d H:i', $apply->create_time),
                'money' => \Yii::$app->formatter->asCurrency($back->money),
                'detail' => $back->des
            ];
            $total += $back->money;
        }
        $arrInfo =  [
            'list' => $data,
            'apply_date' => date('Y年m月d日', $apply->create_time),
            'apply_id' => $apply->apply_id,
            'org_full_name' => $person->org_full_name,
            'person' => $apply->person,
            'bank_name' => $apply->payBack->bank_name,
            'bank_card_id' => $apply->payBack->bank_card_id,
            'des' => $apply->payBack->des ? : '--',
            'approval_person' =>$apply->approval_persons,//多个人、分隔
            'copy_person' => $apply->copy_person ? :  '--',//多个人、分隔
            'caiwu' => $apply->cai_wu_person ? : '',
            'total' => \Yii::$app->formatter->asCurrency($total),
            'total_supper' => CnyLogic::instance()->cny($total)
        ];
        $pdf->createdPdf($root_path, $arrInfo, 'payBack');
        return [
            'path' => $root_path,
            'name' => $apply->apply_id.'.pdf'
        ];
    }
    
    /**
     * 用章
     *
     * @param Apply $apply
     * @return string
     */
    public function useChapter($apply)
    {
        $root_path = $this->getFilePath($apply);
        if(!file_exists($root_path) || \Yii::$app->request->get('debug')){
            $pdf = new MyTcPdf();
            $person = Person::findOne($apply->person_id);
            $arrInfo = [
                'apply_date' => date('Y年m月d日', $apply->create_time),
                'apply_id' => $apply->apply_id,
                'org_full_name' => $person->org_full_name,
                'person' => $person->person_name,
                'chapter_type' => ApplyUseChapter::STATUS[$apply->applyUseChapter->chapter_type],
                'chapter_name' => $apply->applyUseChapter->name,
                'des' => $apply->applyUseChapter->des ?: '--',
                'approval_person' => $apply->approval_persons,//多个人、分隔
                'copy_person' => $apply->copy_person ?: '',//多个人、分隔
            ];
    
            $pdf->createdPdf($root_path, $arrInfo, 'useChapter');
        }
        
        return [
            'path' => $root_path,
            'name' => $apply->apply_id.'.pdf'
        ];
    }
    
    /**
     * PDF 文件路径
     *
     * @param Apply $apply
     *
     * @return string
     */
    protected function getFilePath($apply)
    {
        $basePath = \Yii::$app->basePath.'/web';
        $filePath = '/upload/pdf/';
        $rootPath = $basePath.$filePath;
        if (!file_exists($rootPath)) {
            @mkdir($rootPath, 0777, true);
        }
        $file = $rootPath.$apply->apply_id.'.pdf';
        return $file;
    }
    
    /**
     * 付款申请
     *
     * @param Apply $apply
     *
     * @return string
     */
    public function applyPayPdf($apply)
    {
        $pdf = new MyTcPdf();
        $root_path = $this->getFilePath($apply);
        if(file_exists($root_path)){
            unlink($root_path);
        }
        $person = Person::findOne($apply->person_id);
        $arrInfo = [
            'apply_date' => date('Y年m月d日', $apply->create_time),
            'apply_id' => $apply->apply_id,
            'org_full_name' => $person->org_full_name,
            'person' => $person->person_name,
            'to_name' => $apply->applyPay->to_name,
            'bank_card_id' => $apply->applyPay->bank_card_id,
            'bank_name' => $apply->applyPay->bank_name,
            //'pay_type' => TagTree::findOne($apply->applyPay->pay_type)->name, //申请已去掉
            'money' => \Yii::$app->formatter->asCurrency($apply->applyPay->money),
            'money_supper' => CnyLogic::instance()->cny($apply->applyPay->money),
            'des' => $apply->applyPay->des ? : '--',
            'approval_person' =>$apply->approval_persons,//多个人、分隔
            'copy_person' => $apply->copy_person ? : '--',//多个人、分隔
            'caiwu' => $apply->cai_wu_person ? : ''
        ];
    
        
        $pdf->createdPdf($root_path, $arrInfo, 'applyPay');
        return [
            'path' => $root_path,
            'name' => $apply->apply_id.'.pdf'
        ];
    }
    
    /**
     * 请购
     *
     * @param Apply $apply
     * @return string
     */
    public function applyBuyPdf($apply)
    {
        $pdf = new MyTcPdf();
        $root_path = $this->getFilePath($apply);
        if(file_exists($root_path)){
            unlink($root_path);
        }
        $person = Person::findOne($apply->person_id);
        $arrInfo = [
            'apply_date' => date('Y年m月d日', $apply->create_time),
            'apply_id' => $apply->apply_id,
            'org_full_name' => $person->org_full_name,
            'person' => $person->person_name,
            
            'to_name' => $apply->applyBuy->to_name,
            'bank_card_id' => $apply->applyBuy->bank_card_id,
            'bank_name' => $apply->applyBuy->bank_name,
            
            'des' => $apply->applyBuy->des ? : '--',
            'approval_person' =>$apply->approval_persons,//多个人、分隔
            'copy_person' => $apply->copy_person ? : '--',//多个人、分隔
            'caiwu' => $apply->cai_wu_person ? : ''
        ];
        
        $list = [];
        $total = 0;
        $buyList = ApplyBuyList::find()->where(['apply_id' => $apply->apply_id])->all();
        /**
         * @var ApplyBuyList $v
         */
        foreach ($buyList as $v) {
            $list[] = [
                'asset_type_name' => $v->asset_type_name,
                'asset_brand_name' => $v->asset_brand_name,
                'name' => $v->name,
                'price' => \Yii::$app->formatter->asCurrency($v->price),
                'amount' => $v->amount,
                'total' =>\Yii::$app->formatter->asCurrency($v->price * $v->amount),
            ];
            $price = intval($v->price);
            $total += $price * $v->amount;
        }
        $arrInfo['list'] = $list;
        $arrInfo['total'] =  \Yii::$app->formatter->asCurrency($total);;
        $arrInfo['total_supper'] = CnyLogic::instance()->cny((int)$total);
        
        
        $pdf->createdPdf($root_path, $arrInfo, 'applyBuy');
        return [
            'path' => $root_path,
            'name' => $apply->apply_id.'.pdf'
        ];
    }
    
    /**
     * 需求单
     *
     * @param Apply $apply
     * @return string
     */
    public function applyDemand($apply)
    {
    
        $root_path = $this->getFilePath($apply);
        if(!file_exists($root_path) || \Yii::$app->request->get('debug')) {
            $person = Person::findOne($apply->person_id);
            $arrInfo = [
                'apply_date' => date('Y年m月d日', $apply->create_time),
                'apply_id' => $apply->apply_id,
                'org_full_name' => $person->org_full_name,
                'person' => $person->person_name,
        
                'des' => $apply->applyDemand->des ?: '--',
                'approval_person' => $apply->approval_persons,//多个人、分隔
                'copy_person' => $apply->copy_person ?: '--',//多个人、分隔
            ];
            $list = [];
    
            $demandList = ApplyDemandList::find()->where(['apply_id' => $apply->apply_id])->all();
            /**
             * @var ApplyDemandList $v
             */
            foreach ($demandList as $v) {
                $list[] = [
                    'name' => $v->name,
                    'amount' => $v->amount
                ];
            }
            $arrInfo['list'] = $list;
            $pdf = new MyTcPdf();
            $pdf->createdPdf($root_path, $arrInfo, 'demand');
        }
        return [
            'path' => $root_path,
            'name' => $apply->apply_id.'.pdf'
        ];
    }
    
    /**
     * 资产获取
     *
     * @param $apply
     * @return string
     */
    public function assetGet($apply)
    {
        $person = Person::findOne($apply->person_id);
        $arrInfo = [
            'apply_date' => date('Y年m月d日'),
            'apply_id' => $apply->apply_id,
            'org_full_name' => $person->org_full_name,
            'person' => $person->person_name,
        
            'des' => $apply->assetGet->des ? : '--',
            'approval_person' =>$apply->approval_persons,//多个人、分隔
            'copy_person' => $apply->copy_person ? : '--',//多个人、分隔
        ];
        $list = [];
        $total = 0;
        $assetGetList = AssetGetList::find()->where(['apply_id' => $apply->apply_id])->all();
        /**
         * @var AssetGetList $v
         */
        foreach ($assetGetList as $v) {
            $list[] = [
                'asset_type_name' => $v->asset->asset_type_name,
                'asset_brand_name' => $v->asset->asset_brand_name,
                'name' => $v->asset->name,
                'price' => \Yii::$app->formatter->asCurrency($v->asset->price)
            ];
            $total += $v->asset->price;
        }
        
        $arrInfo['list'] = $list;
        $arrInfo['total'] = \Yii::$app->formatter->asCurrency($total);
        $pdf = new MyTcPdf();
        $root_path = $this->getFilePath($apply);
        $pdf->createdPdf($root_path, $arrInfo, 'assetGet');
        return [
            'path' => $root_path,
            'name' => $apply->apply_id.'.pdf'
        ];
    }
    
    /**
     * 资产归还
     *
     * @param $apply
     * @return string
     */
    public function assetBack($apply)
    {
        $person = Person::findOne($apply->person_id);
        $arrInfo = [
            'apply_date' => date('Y年m月d日'),
            'apply_id' => $apply->apply_id,
            'org_full_name' => $person->org_full_name,
            'person' => $person->person_name,
        
            'des' => $apply->assetBack->des ? : '--',
            'approval_person' =>$apply->approval_persons,//多个人、分隔
            'copy_person' => $apply->copy_person ? : '--',//多个人、分隔
        ];
    
        $list = [];
        $total = 0;
        $assetListIds = explode(',', $apply->assetBack->asset_list_ids);
print_r($assetListIds);
        $assetGetList = AssetList::find()->where(['in', 'id', $assetListIds])->all();
        print_r($assetGetList);
        /**
         * @var AssetList $v
         */
        foreach ($assetGetList as $v) {
            $list[] = [
                'asset_type_name' => $v->asset->asset_type_name,
                'asset_brand_name' => $v->asset->asset_brand_name,
                'name' => $v->asset->name,
                'asset_number' => $v->stock_number,
                'price' => \Yii::$app->formatter->asCurrency($v->asset->price)
            ];
            $total += $v->asset->price;
        }
    
        $arrInfo['list'] = $list;
print_r($list);
        $arrInfo['total'] = \Yii::$app->formatter->asCurrency($total);
    
        $pdf = new MyTcPdf();
        $root_path = $this->getFilePath($apply);
        $pdf->createdPdf($root_path, $arrInfo, 'assetBack');
        return [
            'path' => $root_path,
            'name' => $apply->apply_id.'.pdf'
        ];
    }
    
    /**
     * 转正
     * @param  $apply
     */
    public function applyPositive($apply)
    {
        $pdf = new MyTcPdf();
        $root_path = $this->getFilePath($apply);
        if(file_exists($root_path)){
            unlink($root_path);
        }
        $person = Person::findOne($apply->person_id);
        $arrInfo = [
            'apply_date' => date('Y年m月d日', $apply->create_time),
            'apply_id' => $apply->apply_id,
            'person' => $person->person_name,
            'entry_time' => date('Y年m月d日', strtotime($apply->applyPositive->entry_time)),
            'org' => $apply->applyPositive->org,
            'profession' => $apply->applyPositive->profession,
            'prosecution' => $apply->applyPositive->prosecution,
            'summary' => $apply->applyPositive->summary,
            'suggest' => $apply->applyPositive->suggest,
            'approval_person' =>$apply->approval_persons,//多个人、分隔
            'copy_person' => $apply->copy_person ? : '--',//多个人、分隔
        ];
        
        
        $pdf->createdPdf($root_path, $arrInfo, 'applyPositive');
        return [
            'path' => $root_path,
            'name' => $apply->apply_id.'.pdf'
        ];
    }
    /**
     * 离职
     * @param  $apply
     */
    public function applyLeave($apply)
    {
        $pdf = new MyTcPdf();
        $root_path = $this->getFilePath($apply);
        if(file_exists($root_path)){
            unlink($root_path);
        }
        $person = Person::findOne($apply->person_id);
        $employee = Employee::find()->where(['person_id'=>$apply->person_id])->one();
        $arrInfo = [
            'apply_date' => date('Y年m月d日', $apply->create_time),
            'apply_id' => $apply->apply_id,
            'person' => $person->person_name,
            'assect_list' => AssetLogic::instance()->getAssetHistory($apply->person_id), 
            'finance_list' => JieKuanLogic::instance()->getHistory($apply->person_id),
            'leave_time' => date('Y年m月d日',strtotime($apply->applyLeave->leave_time)),
            'org_name' => $person->org_full_name,
            'prefession' => $person->profession,
            'des' => $apply->applyLeave->des,
            'stock_status' => $apply->applyLeave->stock_status ? '已归还' : '未归还',
            'finance_status' => $apply->applyLeave->finance_status ? '已结算' : '未结算',
            'account_status' => $apply->applyLeave->account_status ? '已交接' : '未交接',
            'work_status' => $apply->applyLeave->work_status ? '已交接' : '未交接',
            'qq' => isset($employee->account)?$employee->account->qq:'--',
	        'email' => isset($employee->account)?$employee->account->email:'--',
	        'phone' => isset($employee->account)?$employee->account->tel:'--',
            
            'approval_person' =>$apply->approval_persons,//多个人、分隔
            'copy_person' => $apply->copy_person ? : '--',//多个人、分隔
        ];
        $pdf->createdPdf($root_path, $arrInfo, 'applyLeave');
        return [
            'path' => $root_path,
            'name' => $apply->apply_id.'.pdf'
        ];
    }
    
    /**
     * 调职
     * @param  $apply
     */
    public function applyTransfer($apply)
    {
        $pdf = new MyTcPdf();
        $root_path = $this->getFilePath($apply);
        if(file_exists($root_path)){
            unlink($root_path);
        }
        $person = Person::findOne($apply->person_id);
        $arrInfo = [
            'apply_date' => date('Y年m月d日', $apply->create_time),
            'apply_id' => $apply->apply_id,
            'person' => $person->person_name,
            'entry_time' => date('Y年m月d日', strtotime($apply->applyTransfer->entry_time)),
            'old_org_name' => OrgLogic::instance()->getOrgName($apply->applyTransfer->old_org_id),
            'target_org_name' => OrgLogic::instance()->getOrgName($apply->applyTransfer->target_org_id),
            'old_profession' => $apply->applyTransfer->old_profession,
            'target_profession' => $apply->applyTransfer->target_profession,
            'transfer_time' => date('Y年m月d日', strtotime($apply->applyTransfer->transfer_time)),
            'des' => $apply->applyTransfer->des,
            
            'approval_person' =>$apply->approval_persons,//多个人、分隔
            'copy_person' => $apply->copy_person ? : '--',//多个人、分隔
        ];
        $pdf->createdPdf($root_path, $arrInfo, 'applyTransfer');
        return [
            'path' => $root_path,
            'name' => $apply->apply_id.'.pdf'
        ];
    }
    /**
     * 开店
     * @param  $apply
     */
    public function applyOpen($apply)
    {
        $pdf = new MyTcPdf();
        $root_path = $this->getFilePath($apply);
        if(file_exists($root_path)){
            unlink($root_path);
        }
        $person = Person::findOne($apply->person_id);
        $arrInfo = [
            'apply_date' => date('Y年m月d日', $apply->create_time),
            'apply_id' => $apply->apply_id,
            'person' => $person->person_name,
            'district_name' => $apply->applyOpen->district_name,
            'address' => $apply->applyOpen->address,
            'rental' => \Yii::$app->formatter->asCurrency($apply->applyOpen->rental),
            'summary' => $apply->applyOpen->summary,
        
            'approval_person' =>$apply->approval_persons,//多个人、分隔
            'copy_person' => $apply->copy_person ? : '--',//多个人、分隔
        ];
        $pdf->createdPdf($root_path, $arrInfo, 'applyOpen');
        return [
            'path' => $root_path,
            'name' => $apply->apply_id.'.pdf'
        ];
    }
}
