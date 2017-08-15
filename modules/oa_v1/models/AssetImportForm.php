<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/8/14
 * Time: 17:40
 */

namespace app\modules\oa_v1\models;


use app\models\Person;
use yii\base\Model;

class AssetImportForm extends Model
{
    public $A;
    public $B;
    public $C;
    public $D;
    public $E;
    public $F;
    public $G;
    public $H;
    public $I;
    public $J;
    public $K;
    
    public function rules()
    {
        return [
            [['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'], 'trim'],
            [['A', 'B', 'C', 'D', 'G', 'J'], 'required'],
            ['G', 'checkStatus']
        ];
    }
    
    public function checkStatus($attribute)
    {
        if (!in_array($this->$attribute, ['未使用', '使用中', '已报废', '已丢失']))  {
            $this->addError($attribute, '无效');
        }
        if($this->$attribute == '使用中'){
            if (!Person::find()->where(['person_name' => $this->H])->one()
                && !Person::find()->where(['person_name' => $this->H, 'phone' => $this->I])->one()) {
                $this->addError($attribute, '使用人不存在');
            }
            
        }
    }
    
    public function attributeLabels()
    {
        return [
            'A' => '类别',
            'B' => '详细类别',
            'C' => '品牌',
            'D' => '名称',
            'E' => '资产编号',
            'F' => 'SN号',
            'G' => '状态',
            'H' => '使用人',
            'I' => '手机号码',
            'J' => '采购价',
            'K' => '说明',
        ];
    }
}