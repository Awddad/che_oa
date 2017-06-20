<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/20
 * Time: 10:58
 */

namespace app\modules\oa_v1\models;


/**
 * 固定资产归还
 *
 * Class AssetBackForm
 * @package app\modules\oa_v1\models
 */
class AssetBackForm extends BaseForm
{
    /**
     * 申请ID
     * @var
     */
    public $apply_id;
    
    public $type = 9;
    
    public $get_person;
    
    public $des = '';
    
    public $files;
    
    public $asset_back_ids;
}