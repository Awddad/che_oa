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
            <td colspan="3">'.$val["detail"].'</td>
            <td colspan="3">'.$val["money"].'</td>
            </tr>';
        }
        $strHtml = <<<TABLEHTML
<style>
.bg{background:rgba(204, 204, 204, 1)}
table tr{height:40px;}
</style>
<div>
    <div>
        <h2 style="text-align: center;">费用报销单</h2> 
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
    
    /**
     * 付款确认
     *
     * @param $param
     * @return string
     */
    public function applyPay($param)
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
            <td style="background-color:#f2f2f2">付款类型</td>
            <td colspan="2">{$param['pay_type']}</td>
            <td style="background-color:#f2f2f2" rowspan="2">金额</td>
            <td>小写</td>
            <td>{$param['money']}</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="2"></td>
            <td>大写</td>
            <td>{$param['money_supper']}</td>
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
            <td></td>
            <td></td>
            <td></td>
            <td rowspan="2">总计</td>
            <td>小写</td>
            <td>{$param['total']}</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
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
<style>
.bg{background:rgba(204, 204, 204, 1)}
table tr{height:40px;}
</style>
<div>
    <h2 style="text-align: center;">需求单</h2>  
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
                <td colspan="2">'.$val['price'].'</td>
            </tr>';
        }
        $strHtml = <<<TABLEHTML
<style>
.bg{background:rgba(204, 204, 204, 1)}
table tr{height:40px;}
</style>
<div>
    <h2 style="text-align: center;">固定资产领取表</h2>  
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
            <td style="background-color:#f2f2f2">类别</td>
            <td colspan="2" style="background-color:#f2f2f2">品牌</td>
            <td style="background-color:#f2f2f2">名称</td>
            <td colspan="2" style="background-color:#f2f2f2">价格</td>
        </tr>
        {$strListHtml}
        <tr>
            <td></td>
            <td colspan="2"></td>
            <td>总计</td>
            <td colspan="2">{$param['total']}</td>
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
<style>
.bg{background:rgba(204, 204, 204, 1)}
table tr{height:40px;}
</style>
<div>
    <h2 style="text-align: center;">固定资产领取表</h2>  
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
<style>
.bg{background:rgba(204, 204, 204, 1)}
table tr{height:40px;}
</style>
<div>
    <h2 style="text-align: center;">转正申请表</h2>
    <table style="text-align: center;line-height:40px;" border="1" width='98%' cellspacing="0">
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
<style>
.bg{background:rgba(204, 204, 204, 1)}
table tr{height:40px;}
</style>
<div>
    <h2 style="text-align: center;">调职申请表</h2>
    <table style="text-align: center;line-height:40px;" border="1" width='98%' cellspacing="0">
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
<style>
.bg{background:rgba(204, 204, 204, 1)}
table tr{height:40px;}
</style>
<div>
    <h2 style="text-align: center;">开店申请表</h2>
    <table style="text-align: center;line-height:40px;" border="1" width='98%' cellspacing="0">
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
            <td style="background-color:#f2f2f2">借款时间</td>
            <td style="background-color:#f2f2f2">借款事由</td>
            <td style="background-color:#f2f2f2">借款金额</td>
            <td style="background-color:#f2f2f2">状态</td>
            </tr>';
        foreach($param['finance_list'] as $v){
            $finance_list .= <<<jdf
        <tr>
            <td >{$v['id']}</td>
            <td >{$v['time']}</td>
            <td >{$v['des']}</td>
            <td colspan="2" align="right">{$v['price']}</td>
            <td >{$v['status']}</td>
        </tr>
jdf;
        }
        
        
        $strHtml = <<<TABLEHTML
<style>
.bg{background:rgba(204, 204, 204, 1)}
table tr{height:40px;}
</style>
<div>
    <h2 style="text-align: center;">转正申请表</h2>
    <table style="text-align: center;line-height:40px;" border="1" width='98%' cellspacing="0">
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
}
