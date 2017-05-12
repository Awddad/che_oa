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
 * @property Apply $apply
 * @property ApprovalLog $nextApprovalLog
 */
class ApprovalLog extends \yii\db\ActiveRecord
{
    const STATUS_PASS = 1;
    const STATUS_FAIL = 2;

    const SCENARIO_FAIL = 'fail';//审批不通过
    const SCENARIO_PASS = 'pass';// 审批通过，需要继续审批
    const SCENARIO_CONFIRM = 'confirm';// 审批通过，需要继续审批，需要财务
    const SCENARIO_COMPLETE = 'complete';// 审批通过，不需要继续审批，申请完成

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

    /**
     * 获取该审批的申请信息
     * @return \yii\db\ActiveQuery|Apply
     */
    public function getApply()
    {
        return $this->hasOne(Apply::className(), ['apply_id' => 'apply_id']);
    }

    /**
     * 获取下一条审批记录
     * @return array|null|self
     */
    public function getNextApprovalLog()
    {
        return self::find()->where(['apply_id' => $this->apply_id, 'steep' => ($this->steep + 1)])->one();
    }

    /**
     * 设置该记录为需要我审核
     */
    public function setApprovalPerson()
    {
        $this->is_to_me_now = true;
        return $this->save();
    }

    public function scenarios()
    {
        return [
        	self::SCENARIO_DEFAULT => ['*'],
            self::SCENARIO_FAIL => ['*'],
            self::SCENARIO_PASS => ['*'],
            self::SCENARIO_CONFIRM => ['*'],
            self::SCENARIO_COMPLETE => ['*'],
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            switch ($this->scenario) {
                case self::SCENARIO_FAIL;
                    return $this->apply->approvalFail();
                    break;
                case self::STATUS_PASS;
                    $nextApproval = $this->nextApprovalLog;
                    return ($nextApproval->setApprovalPerson() && $this->apply->approvalPass($nextApproval->approval_person));
                    break;
                case self::SCENARIO_CONFIRM;
                    return $this->apply->approvalConfirm();
                    break;
                case self::SCENARIO_COMPLETE;
                    return $this->apply->approvalComplete();
                    break;
            }
            return true;
        } else {
            return false;
        }
    }
}
