<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_approval_config_log".
 *
 * @property integer $id
 * @property string $title
 * @property integer $config_id
 * @property string $org_name
 * @property string $apply_name
 * @property string $data
 * @property integer $person_id
 * @property string $person_name
 * @property integer $time
 */
class ApprovalConfigLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_approval_config_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['config_id', 'person_id', 'time'], 'integer'],
            [['data'], 'string'],
            [['title'], 'string', 'max' => 20],
            [['org_name'], 'string', 'max' => 50],
            [['apply_name'], 'string', 'max' => 15],
            [['person_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '操作标题',
            'config_id' => 'oa_approval_config 的id',
            'org_name' => '公司名',
            'apply_name' => '审批类型名字',
            'data' => 'Data',
            'person_id' => '操作者id',
            'person_name' => '操作者姓名',
            'time' => '添加时间',
        ];
    }
}
