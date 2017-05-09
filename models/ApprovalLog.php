<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_approval_log".
 * 申请审批人表
 *
 * @property integer $id
 * @property string $apply_id
 * @property string $approval_person
 * @property integer $approval_person_id
 * @property integer $steep
 * @property integer $is_end
 * @property string $des
 * @property integer $result
 * @property integer $approval_time
 * @property integer $is_to_me_now
 */
class ApprovalLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_approval_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id', 'approval_person', 'approval_person_id'], 'required'],
            [['id', 'approval_person_id', 'steep', 'is_end', 'result', 'approval_time', 'is_to_me_now'], 'integer'],
            [['des'], 'string'],
            [['apply_id'], 'string', 'max' => 20],
            [['approval_person'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增主键',
            'apply_id' => '申请单号，审核单编号',
            'approval_person' => '审批人姓名',
            'approval_person_id' => '审批人id',
            'steep' => '第几步审核',
            'is_end' => '是否是最后一个审核人员',
            'des' => 'Des',
            'result' => '审核结果：
1 - 审核通过
2 - 审核不通过',
            'approval_time' => '审批完成时间',
            'is_to_me_now' => '是否该我审核了:
0 - 不该我审核
1 - 该我审核了
（审核步骤没到我这边或者我已经审核过了的话 值都为0）',
        ];
    }
    
    public function getApply()
    {
    	return $this -> hasOne(Apply::className(), ['apply_id' => 'apply_id']);
    }
}
