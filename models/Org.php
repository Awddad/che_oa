<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_org".
 * 公司组织架构存储，数据同步于权限系统
 *
 * @property integer $org_id
 * @property string $org_name
 * @property integer $pid
 */
class Org extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_org';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['org_id', 'org_name', 'pid'], 'required'],
            [['org_id', 'pid'], 'integer'],
            [['org_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'org_id' => 'Org ID',
            'org_name' => '组织名称',
            'pid' => '父id',
        ];
    }
}
