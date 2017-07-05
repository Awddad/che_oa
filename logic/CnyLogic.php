<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/7/5
 * Time: 10:11
 */

namespace app\logic;


/**
 * 人民币转大写
 * Class CnyLogic
 * @package app\logic
 */
class CnyLogic extends Logic
{
    /**
     * 人民币小写转大写
     * @param $money
     * @return mixed
     */
    public function cny($money)
    {
        static $cnums = ["零", "壹", "贰", "叁", "肆", "伍", "陆", "柒", "捌", "玖"],
        $cnyunits = ["", "圆", "角", "分"],
        $grees = ["", "拾", "佰", "仟", "万", "拾", "佰", "仟", "亿"];
        list($ns1, $ns2) = explode(".", number_format($money, 2, '.', ''), 2);
        
        $ns2 = array_filter(array($ns2[1], $ns2[0])); //转为数组
        
        $arrayTemp = $this->cnyMapUnit(str_split($ns1), $grees);
        
        $ret = array_merge($ns2, array(implode("", $arrayTemp), "")); //处理整数
        
        $arrayTemp = $this->cnyMapUnit($ret, $cnyunits);
        
        $ret = implode("", array_reverse($arrayTemp));     //处理小数
        
        return str_replace(array_keys($cnums), $cnums, $ret);
    }
    
    
    private function cnyMapUnit($list, $units)
    {
        $ul = count($units);
        $xs = array();
        foreach (array_reverse($list) as $x) {
            $l = count($xs);
            
            
            if ($x != "0" || !($l % 4))
                $n = ($x == '0' ? '' : $x) . ($units[($l) % $ul]);
            else {
                if (isset($xs[0][0]))
                    $n = is_numeric($xs[0][0]) ? $x : '';
            }
            array_unshift($xs, $n);
        }
        return $xs;
    }
}