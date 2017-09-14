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
        if(\Yii::$app->request->get('debug')) {
            echo $strHtml;die;
        }
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION);
        $pdf->SetFont('STSongStdLight');//设置宋体，避免中文乱码
    
        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        $pdf->AddPage();
        @$pdf->writeHTML($strHtml, true, true, true, true, '');
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
            <td colspan="3">'.$val["detail"].'</td>
            <td colspan="3">'.$val["money"].'</td>
            </tr>';
        }
        $strHtml = <<<TABLEHTML
<div>
    <div>
        <h2 style="text-align: center;padding-top:0;margin-top:0" >费用报销单</h2> 
        <table style="text-align: center;line-height:24px;" border="1" width='98%' cellspacing="0" bordercolor="rgba(204, 204, 204, 1)">
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
                <td style="background-color:#f2f2f2" colspan="3">事项</td>
                <td style="background-color:#f2f2f2" colspan="3">金额</td>
            </tr>
            {$strListHtml}
            <tr>
                <td colspan="2" rowspan="2" valign="middle">金额合计</td>
                <td>小写</td>
                <td colspan="3">{$arrInfo['total']}</td>
            </tr>
            <tr>
                <td>大写</td>
                <td colspan="3">{$arrInfo['total_supper']}</td>
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
<div>
    <h2 style="text-align: center;">备用金申请单</h2>    
    <table style="text-align: center; line-height:24px;" border="1" width='98%' cellspacing="0" bordercolor="rgba(204, 204, 204, 1)">
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
            <td colspan="3" style="background-color:#f2f2f2">金额</td>
            <td colspan="9" style="background-color:#f2f2f2">事由</td>
        </tr>
        <tr>
            <td colspan="1">小写</td>
            <td colspan="2">{$arrInfo['money']}</td>
            <td colspan="9" rowspan="2">{$arrInfo['detail']}</td>
        </tr>
        <tr>
            <td colspan="1">大写</td>
            <td colspan="2">{$arrInfo['money_supper']}</td>
            <td colspan="9"></td>
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
<div>
    <h2 style="text-align: center;">还款单</h2>  
    <table style="text-align: center;line-height:24px;" border="1" width='98%' cellspacing="0">
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
            <td style="background-color:#f2f2f2" colspan="2">时间</td>
            <td style="background-color:#f2f2f2" colspan="2">金额</td>
            <td style="background-color:#f2f2f2" colspan="2">明细</td>
        </tr>
        {$strListHtml}
        <tr>
            <td style="background-color:#f2f2f2" rowspan="2">金额合计</td>
            <td>小写</td>
            <td colspan="2">{$arrInfo['total']}</td>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td>大写</td>
            <td colspan="2">{$arrInfo['total_supper']}</td>
            <td colspan="2"></td>
        </tr>
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
        return $strHtml;
    }
    
    /**
     * 用章
     *
     * @param $param
     * @return string
     */
    public function useChapter($param)
    {
        $strHtml = <<<TABLEHTML
<div>
    <h2 style="text-align: center;">用章申请单</h2>  
    <table style="text-align: center;line-height:24px;" border="1" width='98%' cellspacing="0">
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
            <td style="background-color:#f2f2f2">用章类型</td>
            <td colspan="2">{$param['use_type']}</td>
            <td style="background-color:#f2f2f2">印章类型</td>
            <td colspan="2">{$param['chapter_type']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2" colspan="1">印章公司</td>
            <td colspan="5">{$param['name']}</td>
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
    
    /**
     * 付款确认
     *
     * @param $param
     * @return string
     */
    public function applyPay($param)
    {
        $strHtml = <<<TABLEHTML
<div>
    <h2 style="text-align: center;">付款单</h2>  
    <table style="text-align: center;line-height:24px;" border="1" width='98%' cellspacing="0">
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
            <td style="background-color:#f2f2f2">对方名称</td>
            <td colspan="2">{$param['to_name']}</td>
            <td style="background-color:#f2f2f2"> 对方银行卡</td>
            <td colspan="2">{$param['bank_card_id']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2" colspan="1">对方开户行</td>
            <td colspan="2">{$param['bank_name']}</td>
            <td style="background-color:#f2f2f2" colspan="1">最晚付款时间</td>
            <td colspan="2">{$param['end_time']}</td>
        </tr>
        <tr>
            <td colspan="2" style="background-color:#f2f2f2" rowspan="2">金额</td>
            <td colspan="2">小写</td>
            <td colspan="2">{$param['money']}</td>
        </tr>
        <tr>
            <td colspan="2">大写</td>
            <td colspan="2">{$param['money_supper']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2" colspan="1">说明</td>
            <td colspan="5">{$param['des']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2">审批人</td>
            <td>{$param['approval_person']}</td>
            <td style="background-color:#f2f2f2">抄送人</td>
            <td>{$param['copy_person']}</td>
            <td style="background-color:#f2f2f2">财务确认</td>
            <td>{$param['caiwu']}</td>
        </tr>
    </table>
</div>
TABLEHTML;
        return $strHtml;
    }
    
    /**
     * 收款确认
     *
     * @param $param
     * @return string
     */
    public function applyBuy($param)
    {
        $strListHtml = '';
        foreach($param['list'] as $val)
        {
            $strListHtml .= '<tr>
                <td>'.$val['asset_type_name'].'</td>
                <td>'.$val['asset_brand_name'].'</td>
                <td>'.$val['name'].'</td>
                <td>'.$val['price'].'</td>
                <td>'.$val['amount'].'</td>
                <td>'.$val['total'].'</td>
            </tr>';
        }
        $strHtml = <<<TABLEHTML
<div>
    <h2 style="text-align: center;">请购单</h2>  
    <table style="text-align: center;line-height:24px;" border="1" width='98%' cellspacing="0">
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
            <td style="background-color:#f2f2f2">对方名称</td>
            <td colspan="2">{$param['to_name']}</td>
            <td style="background-color:#f2f2f2"> 对方银行卡</td>
            <td colspan="2">{$param['bank_card_id']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2" colspan="1">对方开户行</td>
            <td colspan="5">{$param['bank_name']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2">类别</td>
            <td style="background-color:#f2f2f2">品牌</td>
            <td style="background-color:#f2f2f2">名称</td>
            <td style="background-color:#f2f2f2">单价</td>
            <td style="background-color:#f2f2f2">数量</td>
            <td style="background-color:#f2f2f2">小计</td>
        </tr>
        {$strListHtml}
        <tr>
            <td colspan="3" rowspan="2"></td>
            <td rowspan="2">总计</td>
            <td>小写</td>
            <td>{$param['total']}</td>
        </tr>
        <tr>
            <td>大写</td>
            <td>{$param['total_supper']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2" colspan="1">说明</td>
            <td colspan="5">{$param['des']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2">审批人</td>
            <td>{$param['approval_person']}</td>
            <td style="background-color:#f2f2f2">抄送人</td>
            <td>{$param['copy_person']}</td>
            <td style="background-color:#f2f2f2">财务确认</td>
            <td>{$param['caiwu']}</td>
        </tr>
    </table>
</div>
TABLEHTML;
        return $strHtml;
    }
    
    /**
     * 需求单
     *
     * @param $param
     * @return string
     */
    public function demand($param)
    {
        $strListHtml = '';
        foreach($param['list'] as $val)
        {
            $strListHtml .= '<tr>
                <td colspan="3">'.$val['name'].'</td>
                <td colspan="3">'.$val['amount'].'</td>
            </tr>';
        }
        $strHtml = <<<TABLEHTML
<div>
    <h2 style="text-align: center;">需求单</h2>  
    <table style="text-align: center;line-height:24px;" border="1" width='98%' cellspacing="0">
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
            <td colspan="3" style="background-color:#f2f2f2">名称</td>
            <td colspan="3" style="background-color:#f2f2f2">数量</td>
        </tr>
        {$strListHtml}
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
    
    /**
     * 固定资产领取
     * @param $param
     * @return string
     */
    public function assetGet($param)
    {
        $strListHtml = '';
        foreach($param['list'] as $val)
        {
            $strListHtml .= '<tr>
                <td>'.$val['asset_type_name'].'</td>
                <td colspan="2">'.$val['asset_brand_name'].'</td>
                <td>'.$val['name'].'</td>
                <td>'.$val['price'].'</td>
                <td>'.$val['stock_number'].'</td>
            </tr>';
        }
        $strHtml = <<<TABLEHTML
<div>
    <h2 style="text-align: center;">固定资产领取单</h2>  
    <table style="text-align: center;line-height:24px;" border="1" width='98%' cellspacing="0">
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
            <td style="background-color:#f2f2f2">类别</td>
            <td colspan="2" style="background-color:#f2f2f2">品牌</td>
            <td style="background-color:#f2f2f2">名称</td>
            <td colspan="1" style="background-color:#f2f2f2">价格</td>
            <td colspan="1" style="background-color:#f2f2f2">库存编号</td>
        </tr>
        {$strListHtml}
        <tr>
            <td></td>
            <td colspan="2"></td>
            <td>总计</td>
            <td colspan="1">{$param['total']}</td>
            <td></td>
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
    
    /**
     * 固定资产领取
     * @param $param
     * @return string
     */
    public function assetBack($param)
    {
        $strListHtml = '';
        foreach($param['list'] as $val)
        {
            $strListHtml .= '<tr>
                <td colspan="2">'.$val['asset_type_name'].'</td>
                <td>'.$val['asset_brand_name'].'</td>
                <td>'.$val['name'].'</td>
                <td>'.$val['price'].'</td>
                <td>'.$val['asset_number'].'</td>
            </tr>';
        }
        $strHtml = <<<TABLEHTML
<div>
    <h2 style="text-align: center;padding:0;margin:0">固定资产归还单</h2>  
    <table style="text-align: center;line-height:24px;" border="1" width='98%' cellspacing="0">
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
            <td colspan="2"style="background-color:#f2f2f2">类别</td>
            <td style="background-color:#f2f2f2">品牌</td>
            <td style="background-color:#f2f2f2">名称</td>
            <td style="background-color:#f2f2f2">价格</td>
            <td style="background-color:#f2f2f2">库存编号</td>
        </tr>
        {$strListHtml}
        <tr>
            <td colspan="2"></td>
            <td></td>
            <td>总计</td>
            <td>{$param['total']}</td>
            <td></td>
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

    /**
     * 转正
     * 
     * @param $param
     * @return string
     */
    public function applyPositive($param)
    {
        $strHtml = <<<TABLEHTML
<div>
    <h2 style="text-align: center;">转正申请单</h2>
    <table style="text-align: center;line-height:24px;" border="1" width='98%' cellspacing="0">
        <tr>
            <td style="background-color:#f2f2f2">日期</td>
            <td colspan="2">{$param['apply_date']}</td>
            <td style="background-color:#f2f2f2">单号</td>
            <td colspan="2">{$param['apply_id']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 姓名</td>
            <td colspan="2">{$param['person']}</td>
            <td style="background-color:#f2f2f2"> 入职时间</td>
            <td colspan="2">{$param['entry_time']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 试用期部门</td>
            <td colspan="2">{$param['org']}</td>
            <td style="background-color:#f2f2f2"> 试用期职位</td>
            <td colspan="2">{$param['profession']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 生效日期</td>
            <td colspan="5">{$param['positive_time']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 试用期业绩自述</td>
            <td colspan="5">{$param['prosecution']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 试用期工作总结</td>
            <td colspan="5">{$param['summary']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 对公司意见和建议
        </td>
            <td colspan="5">{$param['suggest']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 审批人</td>
            <td colspan="2">{$param['approval_person']}</td>
            <td style="background-color:#f2f2f2"> 抄送人</td>
            <td colspan="2">{$param['copy_person']}</td>
        </tr>
    </table>
</div>
TABLEHTML;
        return $strHtml;
    }

    
    
    /**
     * 调职
     *
     * @param $param
     * @return string
     */
    public function applyTransfer($param)
    {
        $strHtml = <<<TABLEHTML
<div>
    <h2 style="text-align: center;">调职申请单</h2>
    <table style="text-align: center;line-height:24px;" border="1" width='98%' cellspacing="0">
        <tr>
            <td style="background-color:#f2f2f2">日期</td>
            <td colspan="2">{$param['apply_date']}</td>
            <td style="background-color:#f2f2f2">单号</td>
            <td colspan="2">{$param['apply_id']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 姓名</td>
            <td colspan="2">{$param['person']}</td>
            <td style="background-color:#f2f2f2"> 入职时间</td>
            <td colspan="2">{$param['entry_time']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 原部门</td>
            <td colspan="2">{$param['old_org_name']}</td>
            <td style="background-color:#f2f2f2"> 调职后部门</td>
            <td colspan="2">{$param['target_org_name']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 原职位</td>
            <td colspan="2">{$param['old_profession']}</td>
            <td style="background-color:#f2f2f2"> 调职后职位</td>
            <td colspan="2">{$param['target_profession']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 原基本薪资</td>
            <td colspan="2">{$param['old_base_salary']}</td>
            <td style="background-color:#f2f2f2"> 调职后基本薪资</td>
            <td colspan="2">{$param['target_base_salary']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 原绩效薪资</td>
            <td colspan="2">{$param['old_jixiao']}</td>
            <td style="background-color:#f2f2f2"> 调职后效薪资</td>
            <td colspan="2">{$param['target_jixiao']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 调职日期</td>
            <td colspan="5">{$param['transfer_time']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 调职原因</td>
            <td colspan="5">{$param['des']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 审批人</td>
            <td colspan="2">{$param['approval_person']}</td>
            <td style="background-color:#f2f2f2"> 抄送人</td>
            <td colspan="2">{$param['copy_person']}</td>
        </tr>
    </table>
</div>
TABLEHTML;
        return $strHtml;
    }
    
    /**
     * 开店
     *
     * @param $param
     * @return string
     */
    public function applyOpen($param)
    {
        $strHtml = <<<TABLEHTML
<div>
    <h2 style="text-align: center;">开店申请单</h2>
    <table style="text-align: center;line-height:24px;" border="1" width='98%' cellspacing="0">
        <tr>
            <td style="background-color:#f2f2f2">日期</td>
            <td colspan="2">{$param['apply_date']}</td>
            <td style="background-color:#f2f2f2">单号</td>
            <td colspan="2">{$param['apply_id']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 姓名</td>
            <td colspan="2">{$param['person']}</td>
            <td style="background-color:#f2f2f2"> 门店城市</td>
            <td colspan="2">{$param['district_name']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 门店选址</td>
            <td colspan="2">{$param['address']}</td>
            <td style="background-color:#f2f2f2"> 门店租金</td>
            <td colspan="2">{$param['rental']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 门店概述</td>
            <td colspan="5">{$param['summary']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 审批人</td>
            <td colspan="2">{$param['approval_person']}</td>
            <td style="background-color:#f2f2f2"> 抄送人</td>
            <td colspan="2">{$param['copy_person']}</td>
        </tr>
    </table>
</div>
TABLEHTML;
        return $strHtml;
    }
    
    /**
     * 离职
     *
     * @param $param
     * @return string
     */
    public function applyLeave($param)
    {
        $assect_list = '<tr>
            <td style="background-color:#f2f2f2">类别</td>
            <td style="background-color:#f2f2f2">库存编号</td>
            <td style="background-color:#f2f2f2">品牌</td>
            <td style="background-color:#f2f2f2">名称</td>
            <td style="background-color:#f2f2f2">价格</td>
            <td style="background-color:#f2f2f2">状态</td>
            </tr>';
        foreach($param['assect_list'] as $v){
            $assect_list .= <<<jdf
        <tr>
            <td >{$v['type']}</td>
            <td >{$v['sn']}</td>
            <td >{$v['brand']}</td>
            <td >{$v['name']}</td>
            <td align="right">{$v['price']}</td>
            <td >{$v['status']}</td>
        </tr>
jdf;
        }
        $finance_list = '<tr>
            <td style="background-color:#f2f2f2">序号</td>
            <td style="background-color:#f2f2f2">时间</td>
            <td style="background-color:#f2f2f2">事由</td>
            <td style="background-color:#f2f2f2">金额</td>
            <td style="background-color:#f2f2f2">状态</td>
            </tr>';
        foreach($param['finance_list'] as $v){
            $id = $v['id'] + 1;
            $finance_list .= <<<jdf
        <tr>
            <td >{$id}</td>
            <td >{$v['time']}</td>
            <td >{$v['des']}</td>
            <td align="right">{$v['price']}</td>
            <td >{$v['status']}</td>
        </tr>
jdf;
        }
        
        
        $strHtml = <<<TABLEHTML
<div>
    <h2 style="text-align: center;">离职单</h2>
    <table style="text-align: center;line-height:24px;" border="1" width='98%' cellspacing="0">
        <tr>
            <td style="background-color:#f2f2f2">日期</td>
            <td colspan="2">{$param['apply_date']}</td>
            <td style="background-color:#f2f2f2">单号</td>
            <td colspan="2">{$param['apply_id']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 姓名</td>
            <td colspan="2">{$param['person']}</td>
            <td style="background-color:#f2f2f2"> 离职日期</td>
            <td colspan="2">{$param['leave_time']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 部门</td>
            <td colspan="2">{$param['org_name']}</td>
            <td style="background-color:#f2f2f2"> 职位</td>
            <td colspan="2">{$param['prefession']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 离职原因</td>
            <td colspan="5">{$param['des']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 物资是否交还</td>
            <td colspan="5">{$param['stock_status']}</td>
        </tr>
        {$assect_list}
        <tr>
            <td style="background-color:#f2f2f2"> 财务是否结算</td>
            <td colspan="5">{$param['finance_status']}</td>
        </tr>
        {$finance_list}
        <tr>
            <td style="background-color:#f2f2f2"> 账号密码是否交接</td>
            <td colspan="5">{$param['account_status']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 企业qq</td>
            <td colspan="1">{$param['qq']}</td>
            <td style="background-color:#f2f2f2"> 企业邮箱</td>
            <td colspan="1">{$param['email']}</td>
            <td style="background-color:#f2f2f2"> 工作手机号</td>
            <td colspan="1">{$param['phone']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 工作是否交接</td>
            <td colspan="5">{$param['work_status']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 交接人</td>
            <td colspan="5">{$param['handover']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2"> 审批人</td>
            <td colspan="2">{$param['approval_person']}</td>
            <td style="background-color:#f2f2f2"> 抄送人</td>
            <td colspan="2">{$param['copy_person']}</td>
        </tr>
    </table>
</div>
TABLEHTML;
        return $strHtml;
    }
    
    public function applyTravel($param)
    {
        $strListHtml = '';
        foreach($param['travel_list'] as $k => $val)
        {
            $strListHtml .= '<tr>
                <td>'.($k+ 1).'</td>
                <td colspan="2">'.$val['address'].'</td>
                <td colspan="2">'.$val['begin_at']. '~' .$val['end_at'].'</td>
                <td>'.$val['day'].'</td>
            </tr>';
        }
        $strHtml = <<<TABLEHTML
<div>
    <h2 style="text-align: center;">出差申请单</h2>
    <table style="text-align: center;line-height:24px;" border="1" width='98%' cellspacing="0">
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
            <td style="background-color:#f2f2f2">序号</td>
            <td colspan="2" style="background-color:#f2f2f2">出差地点</td>
            <td colspan="2" style="background-color:#f2f2f2">出差时间</td>
            <td style="background-color:#f2f2f2">出差天数</td>
        </tr>
        {$strListHtml}
        <tr>
            <td></td>
            <td colspan="2" ></td>
            <td colspan="2" style="background-color:#f2f2f2">合计</td>
            <td>{$param['total_day']}</td>
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
    
    public function projectRole($param)
    {
        $strListHtml = '';
        $strHtml = <<<TABLEHTML
<div>
    <h2 style="text-align: center;">出差申请单</h2>
    <table style="text-align: center;line-height:24px;" border="1" width='98%' cellspacing="0">
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
            <td style="background-color:#f2f2f2">开通系统</td>
            <td colspan="2">{$param['project_name']}</td>
            <td style="background-color:#f2f2f2">系统角色</td>
            <td colspan="2">{$param['role_name']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2" colspan="1">使用日期</td>
            <td colspan="5">{$param['time']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2" colspan="1">申请说明</td>
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

    public function certificate($param)
    {
        $strHtml = <<<TABLEHTML
<div>
    <h2 style="text-align: center;">用证申请单</h2>
    <table style="text-align: center;line-height:24px;" border="1" width='98%' cellspacing="0">
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
            <td style="background-color:#f2f2f2">证件类型</td>
            <td colspan="2">{$param['type']}</td>
            <td style="background-color:#f2f2f2">用证部门</td>
            <td colspan="2">{$param['to_org']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2" colspan="1">用证时间</td>
            <td colspan="5">{$param['use_time']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2" colspan="1">使用事由</td>
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

    public function holiday($param)
    {
        $strHtml = <<<TABLEHTML
<div>
    <h2 style="text-align: center;">用证申请单</h2>
    <table style="text-align: center;line-height:24px;" border="1" width='98%' cellspacing="0">
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
            <td style="background-color:#f2f2f2">休假类型</td>
            <td colspan="2">{$param['type']}</td>
            <td style="background-color:#f2f2f2">休假时间</td>
            <td colspan="2">{$param['time']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2" colspan="1">休假时长</td>
            <td colspan="5">{$param['duration']}</td>
        </tr>
        <tr>
            <td style="background-color:#f2f2f2" colspan="1">休假事由</td>
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
