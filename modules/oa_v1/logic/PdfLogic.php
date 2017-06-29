<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/29
 * Time: 13:28
 */

namespace app\modules\oa_v1\logic;


use app\logic\Logic;
use app\logic\MyTcPdf;
use app\models\Apply;
use app\models\ApplyUseChapter;
use app\models\BaoXiaoList;
use app\models\JieKuan;
use app\models\Person;
use app\models\TagTree;

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
            'caiwu' => $apply->cai_wu_person ? : '--'
        ];
        $baoXiaoList = BaoXiaoList::find()->where(['apply_id' => $apply->apply_id])->all();
        foreach($baoXiaoList as $v){
            $arrInfo['list'][] = [
                'type_name' => $v['type_name'],
                'money' => \Yii::$app->formatter->asCurrency($v['money']),
                'detail' => @$v['des']
            ];
        }
        
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
            'apply_date' => date('Y年m月d日'),
            'apply_id' => $apply->apply_id,
            'org_full_name' => $person->org_full_name,
            'person' => $person->person_name,
            'bank_name' => $apply->loan->bank_name,
            'bank_card_id' => $apply->loan->bank_card_id,
            'money' => \Yii::$app->formatter->asCurrency($apply->loan->money),
            'detail' => $apply->loan->des,
            'tips' => $apply->loan->tips,
            'approval_person' =>$apply->approval_persons,//多个人、分隔
            'copy_person' => $apply->copy_person ?: '--',//多个人、分隔
            'caiwu' => $apply->cai_wu_person ? : '--'
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
        $getBackList = [];
        $loanIds = explode(',', $apply->payBack->jie_kuan_ids);
        foreach ($loanIds as $apply_id) {
            $back = JieKuan::findOne($apply_id);
            $data[] = [
                'create_time' => date('Y-m-d H:i', $apply->create_time),
                'money' => \Yii::$app->formatter->asCurrency($back->money),
                'detail' => $back->des
            ];
        }
        $arrInfo =  [
            'list' => $getBackList,
            'apply_date' => date('Y年m月d日'),
            'apply_id' => $apply->apply_id,
            'org_full_name' => $person->org_full_name,
            'person' => $apply->person,
            'bank_name' => $apply->payBack->bank_name,
            'bank_card_id' => $apply->payBack->bank_card_id,
            'des' => $apply->payBack->des ? : '--',
            'approval_person' =>$apply->approval_persons,//多个人、分隔
            'copy_person' => $apply->copy_person ? :  '--',//多个人、分隔
            'caiwu' => $apply->cai_wu_person ? : '--'
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
        if(!file_exists($root_path)){
            $pdf = new MyTcPdf();
            $person = Person::findOne($apply->person_id);
            $arrInfo = [
                'apply_date' => date('Y年m月d日'),
                'apply_id' => $apply->apply_id,
                'org_full_name' => $person->org_full_name,
                'person' => $person->person_name,
                'chapter_type' => ApplyUseChapter::STATUS[$apply->applyUseChapter->chapter_type],
                'chapter_name' => $apply->applyUseChapter->name,
                'des' => $apply->applyUseChapter->des ?: '--',
                'approval_person' => $apply->approval_persons,//多个人、分隔
                'copy_person' => $apply->copy_person ?: '--',//多个人、分隔
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
        $filePath = '/upload/pdf/loan/'.date('Y-m-d').'/';
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
        $person = Person::findOne($apply->person_id);
        $arrInfo = [
            'apply_date' => date('Y年m月d日'),
            'apply_id' => $apply->apply_id,
            'org_full_name' => $person->org_full_name,
            'person' => $person->person_name,
            'to_name' => $apply->applyPay->to_name,
            'bank_card_id' => $apply->applyPay->bank_card_id,
            'bank_name' => $apply->applyPay->bank_name,
            'pay_type' => TagTree::findOne($apply->applyPay->pay_type)->name,
            'money' => $apply->applyPay->money,
            'des' => $apply->applyUseChapter->des ? : '--',
            'approval_person' =>$apply->approval_persons,//多个人、分隔
            'copy_person' => $apply->copy_person ? : '--',//多个人、分隔
            'caiwu' => $apply->cai_wu_person ? : '--'
        ];
    
        
        $pdf->createdPdf($root_path, $arrInfo, 'useChapter');
        return [
            'path' => $root_path,
            'name' => $apply->apply_id.'.pdf'
        ];
    }
    
    /**
     * 请购
     *
     * @param $apply
     * @return string
     */
    public function applyBuyPdf($apply)
    {
        $person = Person::findOne($apply->person_id);
        $arrInfo = [
            'apply_date' => date('Y年m月d日'),
            'apply_id' => $apply->apply_id,
            'org_full_name' => $person->org_full_name,
            'person' => $person->person_name,
        
            'des' => $apply->applyUseChapter->des ? : '--',
            'approval_person' =>$apply->approval_persons,//多个人、分隔
            'copy_person' => $apply->copy_person ? : '--',//多个人、分隔
        ];
    
        $pdf = new MyTcPdf();
        $root_path = $this->getFilePath($apply);
        $pdf->createdPdf($root_path, $arrInfo, 'useChapter');
        return [
            'path' => $root_path,
            'name' => $apply->apply_id.'.pdf'
        ];
    }
    
    /**
     * 需求单
     *
     * @param $apply
     * @return string
     */
    public function applyDemand($apply)
    {
        $person = Person::findOne($apply->person_id);
        $arrInfo = [
            'apply_date' => date('Y年m月d日'),
            'apply_id' => $apply->apply_id,
            'org_full_name' => $person->org_full_name,
            'person' => $person->person_name,
        
            'des' => $apply->applyUseChapter->des ? : '--',
            'approval_person' =>$apply->approval_persons,//多个人、分隔
            'copy_person' => $apply->copy_person ? : '--',//多个人、分隔
        ];
    
        $pdf = new MyTcPdf();
        $root_path = $this->getFilePath($apply);
        $pdf->createdPdf($root_path, $arrInfo, 'useChapter');
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
        
            'des' => $apply->applyUseChapter->des ? : '--',
            'approval_person' =>$apply->approval_persons,//多个人、分隔
            'copy_person' => $apply->copy_person ? : '--',//多个人、分隔
        ];
    
        $pdf = new MyTcPdf();
        $root_path = $this->getFilePath($apply);
        $pdf->createdPdf($root_path, $arrInfo, 'useChapter');
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
        
            'des' => $apply->applyUseChapter->des ? : '--',
            'approval_person' =>$apply->approval_persons,//多个人、分隔
            'copy_person' => $apply->copy_person ? : '--',//多个人、分隔
        ];
    
        $pdf = new MyTcPdf();
        $root_path = $this->getFilePath($apply);
        $pdf->createdPdf($root_path, $arrInfo, 'useChapter');
        return [
            'path' => $root_path,
            'name' => $apply->apply_id.'.pdf'
        ];
    }
    
}