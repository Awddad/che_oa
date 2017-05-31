<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/9
 * Time: 17:18
 */

namespace app\modules\oa_v1\logic;


use app\logic\Logic;
use yii\data\Pagination;

/**
 * 逻辑基础方法
 *
 * Class BaseLogic
 * @package app\modules\oa_v1\logic
 */
class BaseLogic extends Logic
{
    /**
     * 分页数据处理
     *
     * @param Pagination $pagination
     * @return array
     */
    public function pageFix($pagination)
    {
        return [
            'totalCount' => intval($pagination->totalCount),
            'pageCount' => intval($pagination->getPageCount()),
            'currentPage' => intval($pagination->getPage() + 1),
            'perPage' => intval($pagination->getPageSize()),
        ];
    }
}