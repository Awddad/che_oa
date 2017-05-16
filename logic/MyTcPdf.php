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
        $strListHtml = '<tr><th>类别</th><th>金额</th><th>明细</th></tr>';
        foreach($arrInfo['list'] as $val)
        {
            $strListHtml .= "<tr><td>{$val['type_name']}</td><td>{$val['money']}</td><td>{$val['detail']}</td></tr>";
        }
        $strHtml = <<<TABLEHTML
<style>
</style>
<div>
    <div>
        <table>
            <tr>
                <td>日期</td>
                <td>{$arrInfo['apply_date']}</td>
                <td>单号</td>
                <td>{$arrInfo['apply_id']}</td>
            </tr>
            <tr>
                <td>部门</td>
                <td>{$arrInfo['org_full_name']}</td>
                <td>报销人</td>
                <td>{$arrInfo['person']}</td>
            </tr>
            <tr>
                <td>开户行名称</td>
                <td>{$arrInfo['bank_name']}</td>
                <td>银行卡号</td>
                <td>{$arrInfo['bank_card_id']}</td>
            </tr>
        </table>
    </div>
    <div>
        <table>
            {$strListHtml}
        </table>
    </div>
    <div>
        <table>
            <tr>
                <td>备注信息</td>
                <td>{$arrInfo['tips']}</td>
            </tr>
        </table>
    </div>
    <div>
        <table>
            <tr>
                <td>审批人</td>
                <td>{$arrInfo['approval_person']}</td>
                <td>抄送人</td>
                <td>{$arrInfo['copy_person']}</td>
                <td>财务确认</td>
                <td></td>
            </tr>
        </table>
    </div>
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
     * @功能：              生成借款单PDF文件
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
</style>
<div>
    <div>
        <table>
            <tr>
                <td>日期</td>
                <td>{$arrInfo['apply_date']}</td>
                <td>单号</td>
                <td>{$arrInfo['apply_id']}</td>
            </tr>
            <tr>
                <td>部门</td>
                <td>{$arrInfo['org_full_name']}</td>
                <td>报销人</td>
                <td>{$arrInfo['person']}</td>
            </tr>
            <tr>
                <td>开户行名称</td>
                <td>{$arrInfo['bank_name']}</td>
                <td>银行卡号</td>
                <td>{$arrInfo['bank_card_id']}</td>
            </tr>
        </table>
    </div>
    <div>
        <table>
            <tr>
                <th>借款金额</th>
                <th>事由</th>
            </tr>
            <tr>
                <td>￥{$arrInfo['money']}</td>
                <td>{$arrInfo['detail']}</td>
            </tr>
            <tr>
                <td>备注</td>
                <td>{$arrInfo['tips']}</td>
            </tr>
        </table>
    </div>
    <div>
        <table>
            <tr>
                <td>审批人</td>
                <td>{$arrInfo['approval_person']}</td>
                <td>抄送人</td>
                <td>{$arrInfo['copy_person']}</td>
                <td>财务确认</td>
                <td></td>
            </tr>
        </table>
    </div>
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
        $strListHtml = '<tr><th>类别</th><th>金额</th><th>明细</th></tr>';
        foreach($arrInfo['list'] as $val)
        {
            $strListHtml .= "<tr><td>{$val['type_name']}</td><td>{$val['money']}</td><td>{$val['detail']}</td></tr>";
        }
        $strHtml = <<<TABLEHTML
<style>
</style>
<div>
    <div>
        <table>
            <tr>
                <td>日期</td>
                <td>{$arrInfo['apply_date']}</td>
                <td>单号</td>
                <td>{$arrInfo['apply_id']}</td>
            </tr>
            <tr>
                <td>部门</td>
                <td>{$arrInfo['org_full_name']}</td>
                <td>报销人</td>
                <td>{$arrInfo['person']}</td>
            </tr>
            <tr>
                <td>开户行名称</td>
                <td>{$arrInfo['bank_name']}</td>
                <td>银行卡号</td>
                <td>{$arrInfo['bank_card_id']}</td>
            </tr>
        </table>
    </div>
    <div>
        <table>
            {$strListHtml}
        </table>
    </div>
    <div>
        <table>
            <tr>
                <td>备注信息</td>
                <td>{$arrInfo['tips']}</td>
            </tr>
        </table>
    </div>
    <div>
        <table>
            <tr>
                <td>审批人</td>
                <td>{$arrInfo['approval_person']}</td>
                <td>抄送人</td>
                <td>{$arrInfo['copy_person']}</td>
                <td>财务确认</td>
                <td></td>
            </tr>
        </table>
    </div>
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
    
}
