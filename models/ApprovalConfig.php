<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "oa_approval_config".
 *
 * @property integer $id
 * @property integer $apply_type
 * @property string $apply_name
 * @property integer $org_id
 * @property string $org_name
 * @property integer $type
 * @property string $approval
 * @property string $copy_person
 * @property integer $copy_person_count
 * @property integer $created_at
 * @property integer $updated_at
 */
class ApprovalConfig extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_approval_config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_type', 'org_id', 'type', 'copy_person_count', 'created_at', 'updated_at'], 'integer'],
            [['approval'], 'string'],
            [['apply_name'], 'string', 'max' => 15],
            [['org_name'], 'string', 'max' => 100],
            [['copy_person'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'apply_type' => '审批类型',
            'apply_name' => '审批类型名',
            'org_id' => '组织id',
            'org_name' => '公司名',
            'type' => '0：不分条件   1：分条件',
            'approval' => '审批人配置 json',
            'copy_person' => '抄送人配置',
            'copy_person_count' => '抄送人总数',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
