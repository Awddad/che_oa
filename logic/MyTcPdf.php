<?php
namespace app\logic;
/**
 * php生成与PDF类封装
 * @author：王雕
 *@创建时间：2017-05-05
 */
use TCPDF;
class MyTcPdf {
    
    /**
     * 创建PDF
     *
     * @param $pdfName
     * @param $param
     * @param $type
     *
     * @return  boolean
     */
    public function createdPdf($pdfName, $param, $type)
    {
        if(pathinfo($pdfName, PATHINFO_EXTENSION) != 'pdf')
        {
            return false;//文件类型不是pdf
        }
        $strHtml = $this->$type($param);
        $pdf = new TCPDF();
        $pdf->SetFont('STSongStdLight');//设置宋体，避免中文乱码
        $pdf->AddPage();
        $pdf->writeHTML($strHtml, true, false, true, false, '');
        $pdf->lastPage();
        $pdf->Output($pdfName, 'F');//只保存 F    保存与输出 FI 只输出I
        return is_file($pdfName);
    }
    
    
    /**
     * @功能：              生成报销单PDF文件
     * @作者：              王雕
     * @创建时间：          2017-05-15
     * @param array $arrInfo
     *
     * @return boolean
     */
    public function baoxiao($arrInfo)
    {
        $strListHtml = '';
        foreach($arrInfo['list'] as $val)
        {
            $strListHtml .= '<tr>
            <td colspan="2" >'.$val["type_name"].'</td>
            <td colspan="2">'.$val["money"].'</td>
            <td colspan="2">'.$val["detail"].'</td>
            </tr>';
        }
        $strHtml = <<<TABLEHTML
<style>
.bg{background:rgba(204, 204, 204, 1)}
table tr{height:40px;}
</style>
<div>
    <div>
        <h2 style="text-align: center;">报销单</h2> 
        <table style="text-align: center;line-height:40px;" border="1" width='98%' cellspacing="0" bordercolor="rgba(204, 204, 204, 1)">
            <tr>
                <td style="background-color:#f2f2f2">日期</td>
                <td colspan="2">{$arrInfo['apply_date']}</td>
                <td style="background-color:#f2f2f2">单号</td>
                <td colspan="2">{$arrInfo['apply_id']}</td>
            </tr>
            <tr>
                <td style="background-color:#f2f2f2">部门</td>
                <td colspan="2">{$arrInfo['org_full_name']}</td>
                <td style="background-color:#f2f2f2">报销人</td>
                <td colspan="2">{$arrInfo['person']}</td>
            </tr>
            <tr>
                <td style="background-color:#f2f2f2">开户行名称</td>
                <td colspan="2">{$arrInfo['bank_name']}</td>
                <td style="background-color:#f2f2f2">银行卡号</td>
                <td colspan="2">{$arrInfo['bank_card_id']}</td>
            </tr>
            <tr>
                <td style="background-color:#f2f2f2" colspan="2">类别</td>
                <td style="background-color:#f2f2f2" colspan="2">金额</td>
                <td style="background-color:#f2f2f2" colspan="2">明细</td>
            </tr>
            {$strListHtml}
            <tr>
                <td style="background-color:#f2f2f2">审批人</td>
                <td>{$arrInfo['approval_person']}</td>
                <td style="background-color:#f2f2f2">抄送人</td>
                <td>{$arrInfo['copy_person']}</td>
                <td style="background-color:#f2f2f2">财务确认</td>
                <td>{$arrInfo['caiwu']}</td>
            </tr>
        </table>
    </div>
</div>
TABLEHTML;
        return $strHtml;
    }
    
    
    /**
     * @功能：              生成借款单PDF文件
     * @作者：              王雕
     * @创建时间：          2017-05-15
     * @param array $arrInfo
     * @return boolean
     */
    public function loan($arrInfo)
    {
        $strHtml = <<<TABLEHTML
<style>
.bg{background:rgba(204, 204, 204, 1)}
table tr{height:40px;}
</style>
<div>
    <h2 style="text-align: center;">借款单</h2>    
    <table style="text-align: center; line-height:40px;" border="1" width='98%' cellspacing="0" bordercolor="rgba(204, 204, 204, 1)">
        <tr>
            <td style="background-color:#f2f2f2" colspan="2">日期</td>
            <td colspan="4">{$arrInfo['apply_date']}</td>
            <td style="background-color:#f2f2f2" colspan="2">单号</td>
            <td colspan="4">{$arrInfo['apply_id']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2" colspan="2">部门</td>
            <td colspan="4">{$arrInfo['org_full_name']}</td>
            <td style="background-color:#f2f2f2" colspan="2">报销人</td>
            <td colspan="4">{$arrInfo['person']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2" colspan="2">开户行名称</td>
            <td colspan="4">{$arrInfo['bank_name']}</td>
            <td style="background-color:#f2f2f2" colspan="2">银行卡号</td>
            <td colspan="4">{$arrInfo['bank_card_id']}</td>
        </tr>
        <tr>
            <td colspan="3" style="background-color:#f2f2f2">借款金额</td>
            <td colspan="9" style="background-color:#f2f2f2">事由</td>
        </tr>
        <tr>
            <td colspan="3">￥{$arrInfo['money']}</td>
            <td colspan="9">{$arrInfo['detail']}</td>
        </tr>
        <tr>
            <td colspan="3" style="background-color:#f2f2f2">备注</td>
            <td colspan="9">{$arrInfo['tips']}</td>
        </tr>
        <tr>
            <td colspan="2" style="background-color:#f2f2f2">审批人</td>
            <td colspan="2">{$arrInfo['approval_person']}</td>
            <td colspan="2" style="background-color:#f2f2f2">抄送人</td>
            <td colspan="2">{$arrInfo['copy_person']}</td>
            <td colspan="2" style="background-color:#f2f2f2">财务确认</td>
            <td colspan="2"> {$arrInfo['caiwu']}</td>
        </tr>
    </table>
</div>
TABLEHTML;
        return $strHtml;
    }
    
    /**
     * @功能：              生成报销单PDF文件
     * @作者：              王雕
     * @创建时间：          2017-05-15
     * @param array $arrInfo
     *
     * @return boolean
     */
    public function payBack($arrInfo)
    {
        // html 具体样式等前端提供，此处先写个demo
        $strListHtml = '';
        foreach($arrInfo['list'] as $val)
        {
            $strListHtml .= '<tr>
                <td colspan="2">'.$val['create_time'].'</td>
                <td colspan="2">'.$val['money'].'</td>
                <td colspan="2">'.$val['detail'].'</td>
            </tr>';
        }
        $strHtml = <<<TABLEHTML
<style>
.bg{background:rgba(204, 204, 204, 1)}
table tr{height:40px;}
</style>
<div>
    <h2 style="text-align: center;">还款单</h2>  
    <table style="text-align: center;line-height:40px;" border="1" width='98%' cellspacing="0">
        <tr>
            <td style="background-color:#f2f2f2">日期</td>
            <td colspan="2">{$arrInfo['apply_date']}</td>
            <td style="background-color:#f2f2f2">单号</td>
            <td colspan="2">{$arrInfo['apply_id']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2">部门</td>
            <td colspan="2">{$arrInfo['org_full_name']}</td>
            <td style="background-color:#f2f2f2"> 还款人</td>
            <td colspan="2">{$arrInfo['person']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2">开户行名称</td>
            <td colspan="2">{$arrInfo['bank_name']}</td>
            <td style="background-color:#f2f2f2">银行卡号</td>
            <td colspan="2">{$arrInfo['bank_card_id']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2" colspan="2">借款时间</td>
            <td style="background-color:#f2f2f2" colspan="2">金额</td>
            <td style="background-color:#f2f2f2" colspan="2">明细</td>
        </tr>
        {$strListHtml}
        <tr>
            <td style="background-color:#f2f2f2" colspan="2">备注信息</td>
            <td colspan="4">{$arrInfo['des']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2">审批人</td>
            <td>{$arrInfo['approval_person']}</td>
            <td style="background-color:#f2f2f2">抄送人</td>
            <td>{$arrInfo['copy_person']}</td>
            <td style="background-color:#f2f2f2">财务确认</td>
            <td>{$arrInfo['caiwu']}</td>
        </tr>
    </table>
</div>
TABLEHTML;
        return $strListHtml;
    }
    
    
    public function useChapter($param)
    {
        $strHtml = <<<TABLEHTML
<style>
.bg{background:rgba(204, 204, 204, 1)}
table tr{height:40px;}
</style>
<div>
    <h2 style="text-align: center;">用章申请</h2>  
    <table style="text-align: center;line-height:40px;" border="1" width='98%' cellspacing="0">
        <tr>
            <td style="background-color:#f2f2f2">日期</td>
            <td colspan="2">{$param['apply_date']}</td>
            <td style="background-color:#f2f2f2">单号</td>
            <td colspan="2">{$param['apply_id']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2">部门</td>
            <td colspan="2">{$param['org_full_name']}</td>
            <td style="background-color:#f2f2f2"> 姓名</td>
            <td colspan="2">{$param['person']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2">印章类型</td>
            <td colspan="2">{$param['chapter_type']}</td>
            <td style="background-color:#f2f2f2"> 印章名称</td>
            <td colspan="2">{$param['chapter_name']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2" colspan="1">说明</td>
            <td colspan="5">{$param['des']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2">审批人</td>
            <td colspan="2">{$param['approval_person']}</td>
            <td style="background-color:#f2f2f2">抄送人</td>
            <td colspan="2">{$param['copy_person']}</td>
        </tr>
    </table>
</div>
TABLEHTML;
        return $strHtml;
    }
}
