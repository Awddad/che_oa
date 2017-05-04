<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_dd_type".
 * 财务类型，如报销的时候：住宿报销 吃饭报销  打车报销等等，数据要从财务那边获取
 * 
 * @property integer $id
 * @property string $name
 */
class DdType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_dd_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'name'], 'required'],
            [['id'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'name' => '名称',
        ];
    }
}
