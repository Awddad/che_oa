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
            'caiwu' => $person->person_name
        ];
        $baoXiaoList = BaoXiaoList::find()->where(['apply_id' => $apply->apply_id])->all();
        foreach($baoXiaoList as $v){
            $arrInfo['list'][] = [
                'type_name' => $v['type_name'],
                'money' => \Yii::$app->formatter->asCurrency($v['money']),
                'detail' => @$v['des']
            ];
        }
        $myPdf = new MyTcPdf();
        $root_path = $this->getFilePath($apply, true);
        $myPdf->createdPdf($root_path, $arrInfo, 'baoxiao');
        
        return '/web/pdf/'.$apply->apply_id.'.pdf';
    }
    
    /**
     * 借款单
     *
     * @param Apply $apply
     * @return string
     */
    public function loanPdf($apply)
    {
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
            'caiwu' => $person->person_name
        ];
        $pdf = new MyTcPdf();
        $root_path = $this->getFilePath($apply, true);
        $pdf->createdPdf($root_path, $arrInfo, 'loan');
        return '/web/pdf/'.$apply->apply_id.'.pdf';
    }
    
    /**
     * 还款单
     *
     * @param Apply $apply
     * @return string
     */
    public function payBackPdf($apply)
    {
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
            'caiwu' => '--'
        ];
        $pdf = new MyTcPdf();
        $root_path = $this->getFilePath($apply, true);
        $pdf->createdPdf($root_path, $arrInfo, 'payBack');
        return '/web/pdf/'.$apply->apply_id.'.pdf';
    }
    
    /**
     * 用章
     *
     * @param Apply $apply
     * @return string
     */
    public function useChapter($apply)
    {
        if($apply->apply_list_pdf && !\Yii::$app->request->get('debug')) {
            return $apply->apply_list_pdf;
        }
        $person = Person::findOne($apply->person_id);
        $arrInfo = [
            'apply_date' => date('Y年m月d日'),
            'apply_id' => $apply->apply_id,
            'org_full_name' => $person->org_full_name,
            'person' => $person->person_name,
            'chapter_type' => ApplyUseChapter::STATUS[$apply->applyUseChapter->chapter_type],
            'chapter_name' => $apply->applyUseChapter->name,
            'des' => $apply->applyUseChapter->des ? : '--',
            'approval_person' =>$apply->approval_persons,//多个人、分隔
            'copy_person' => $apply->copy_person ? : '--',//多个人、分隔
        ];
    
        $pdf = new MyTcPdf();
        $root_path = $this->getFilePath($apply, true);
        $pdf->createdPdf($root_path, $arrInfo, 'useChapter');
        return '/web/pdf/'.$apply->apply_id.'.pdf';
    }
    
    
    /**
     * PDF 文件路径
     *
     * @param $apply
     * @param bool $flag 是否需要重新生成
     *
     * @return string
     */
    protected function getFilePath($apply, $flag = false)
    {
        if($apply->apply_list_pdf) {
            $root_path = \Yii::$app -> basePath.'/web'.$apply->apply_list_pdf;
            if($flag) {
                if(file_exists($root_path)){
                    unlink($root_path);
                }
            }
        } else {
            $root_path = \Yii::$app->basePath . '/web/pdf/'.date('Ymd'). '/' . $apply->apply_id . '.pdf';
            if (!file_exists($root_path)) {
                @mkdir($root_path, 0777, true);
            }
        }
        
        return $root_path;
    }
}