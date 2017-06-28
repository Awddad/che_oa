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
     * @功能：              生成报销单PDF文件
     * @作者：              王雕
     * @创建时间：          2017-05-15
     * @param array $strSaveName   保存的文件名 
     * @param array $arrInfo       格式：$arrInfo = [
                                        'apply_date' => '2017年5月5日',
                                        'apply_id' => '20170505102037012134', 
                                        'org_full_name' => '南京汽车销售有限公司-中规车一区-涟水店', 
                                        'person' => '马聪', 
                                        'bank_name' => '中国银行丰庄支行', 
                                        'bank_card_id' => '622262132132141241451', 
                                        'list' => [
                                            [
                                                'type_name' => '差旅费', 
                                                'money' => '1182.00', 
                                                'detail' => '两天的差旅费，具体项目见附件明细'
                                            ],
                                            //....
                                        ], 
                                        'approval_person' => '陈贵、李财',//多个人、分隔 
                                        'copy_person' => '张三',//多个人、分隔 
                                    ];
     */
    public function createBaoXiaoDanPdf($strSaveName, $arrInfo)
    {
        if(pathinfo($strSaveName, PATHINFO_EXTENSION) != 'pdf')
        {
            return false;//文件类型不是pdf
        }
        // html 具体样式等前端提供，此处先写个demo
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
                <td>财务确认</td>
            </tr>
        </table>
    </div>
</div>
TABLEHTML;
        $pdf = new TCPDF();
        $pdf->SetFont('STSongStdLight');//设置宋体，避免中文乱码
        $pdf->AddPage();
        $pdf->writeHTML($strHtml, true, false, false, false, '');
        $pdf->lastPage();
        $pdf->Output($strSaveName, 'F');//只保存 F    保存与输出 FI 只输出I
        return is_file($strSaveName);
    }
    
    
    /**
     * @功能：              生成借款单PDF文件
     * @作者：              王雕
     * @创建时间：          2017-05-15
     * @param string $strSaveName   保存的文件名
     * @param array $arrInfo       格式：$arrInfo = [
                                        'apply_date' => '2017年5月5日',
                                        'apply_id' => '20170505102037012134', 
                                        'org_full_name' => '南京汽车销售有限公司-中规车一区-涟水店', 
                                        'person' => '马聪', 
                                        'bank_name' => '中国银行丰庄支行', 
                                        'bank_card_id' => '622262132132141241451', 
                                        'money' => '1265.00', 
                                        'detail' => '还款测试', 
                                        'tips' => '你小样', 
                                        'approval_person' => '陈贵、李财',//多个人、分隔 
                                        'copy_person' => '张三',//多个人、分隔 
                                    ];
     */
    public function createJieKuanDanPdf($strSaveName, $arrInfo)
    {
        if(pathinfo($strSaveName, PATHINFO_EXTENSION) != 'pdf')
        {
            return false;//文件类型不是pdf
        }
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
            <td colspan="2"> 财务确认</td>
        </tr>
    </table>
</div>
TABLEHTML;
        $pdf = new TCPDF();
        $pdf->SetFont('STSongStdLight');//设置宋体，避免中文乱码
        $pdf->AddPage();
        $pdf->writeHTML($strHtml, true, false, true, false, '');
        $pdf->lastPage();
        $pdf->Output($strSaveName, 'F');//只保存 F    保存与输出 FI 只输出I    
        return is_file($strSaveName);
    }
    
/**
     * @功能：              生成报销单PDF文件
     * @作者：              王雕
     * @创建时间：          2017-05-15
     * @param string $strSaveName   保存的文件名
     * @param array $arrInfo       格式：$arrInfo = [
                                        'apply_date' => '2017年5月5日',
                                        'apply_id' => '20170505102037012134', 
                                        'org_full_name' => '南京汽车销售有限公司-中规车一区-涟水店', 
                                        'person' => '马聪', 
                                        'bank_name' => '中国银行丰庄支行', 
                                        'bank_card_id' => '622262132132141241451', 
                                        'list' => [
                                            [
                                                'create_time' => '2017-05-12 12:12',
                                                'money' => '1182.00', 
                                                'detail' => '两天的差旅费，具体项目见附件明细'
                                            ],
                                            //....
                                        ], 
                                        'tips' => '备注信息', 
                                        'approval_person' => '陈贵、李财',//多个人、分隔 
                                        'copy_person' => '张三',//多个人、分隔 
                                    ];
     */
    public function createHuanKuanDanPdf($strSaveName, $arrInfo)
    {
        if(pathinfo($strSaveName, PATHINFO_EXTENSION) != 'pdf')
        {
            return false;//文件类型不是pdf
        }
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
            <td>财务确认</td>
        </tr>
    </table>
</div>
TABLEHTML;
        $pdf = new TCPDF();
        $pdf->SetFont('STSongStdLight');//设置宋体，避免中文乱码
        $pdf->AddPage();
        $pdf->writeHTML($strHtml, true, false, true, false, '');
        $pdf->lastPage();
        $pdf->Output($strSaveName, 'F');//只保存 F    保存与输出 FI 只输出I
        return is_file($strSaveName);
    }
    
    
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
