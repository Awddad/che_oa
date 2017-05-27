<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply".
 * 申请主表
 *
 * @property string $apply_id
 * @property integer $create_time
 * @property integer $type
 * @property string $title
 * @property string $person
 * @property integer $person_id
 * @property string $approval_persons
 * @property string $copy_person
 * @property integer $status
 * @property string $next_des
 * @property integer $cai_wu_need
 * @property string $cai_wu_person
 * @property integer $cai_wu_person_id
 * @property integer $cai_wu_time
 * @property integer $apply_list_pdf
 * @property integer $org_id
 */
class Apply extends \yii\db\ActiveRecord
{
    const TYPE_BAO_XIAO = 1;
    const TYPE_JIE_KUAN = 2;
    const TYPE_HUAN_KUAN = 3;

    const STATUS_WAIT = 1;//等待审核
    const STATUS_ING = 11;//审核中
    const STATUS_FAIL = 2;//审核失败
    const STATUS_REVOKED = 3;//申请撤销
    const STATUS_CONFIRM = 4;//财务确认
    const STATUS_OK = 99;//申请撤销

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id', 'type', 'title', 'person', 'person_id', 'approval_persons'], 'required'],
            [['create_time', 'type', 'person_id', 'status', 'cai_wu_need', 'cai_wu_person_id', 'cai_wu_time', 'org_id'], 'integer'],
            [['apply_id'], 'string', 'max' => 20],
            [['title', 'person', 'approval_persons', 'copy_person', 'next_des', 'cai_wu_person', 'apply_list_pdf'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'apply_id' => '申请id - 审批单编号',
            'create_time' => '申请发起时间',
            'type' => '申请类型：
1 - 报销申请
2 - 借款申请
3 - 还款申请',
            'title' => '申请标题',
            'person' => '申请发起人姓名',
            'person_id' => '申请发起人的uid',
            'approval_persons' => '审批人姓名，多个用逗号分隔',
            'copy_person' => '抄送人员姓名，多个用逗号分隔',
            'status' => '申请状态：
1 -  刚创建，等待审核中
2 -  审核未通过
3 -  撤销申请
4 -  等待财务确认中
99 -  审核通过
',
            'next_des' => '下一步的描述，如：待 ** 审批',
            'cai_wu_need' => '是否需要财务确认, 
1 - 不需要
2 - 需要

（不需要财务确认的 cai_wu相关字段都无用）',
            'cai_wu_person' => '财务确认人姓名',
            'cai_wu_person_id' => '财务确认人id',
            'cai_wu_time' => '财务确认时间',
        ];
    }

    /**
     * 报销
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExpense()
    {
        return $this->hasOne(BaoXiao::className(), ['apply_id' => 'apply_id']);
    }

    /**
     * 借款
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLoan()
    {
        return $this->hasOne(JieKuan::className(), ['apply_id' => 'apply_id']);
    }

    /**
     * 还款
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPayBack()
    {
        return $this->hasOne(PayBack::className(), ['apply_id' => 'apply_id']);
    }

    /*
     * 获取申请表员工的信息
     * @return \yii\db\ActiveQuery
     */
    public function getPersonInfo()
    {
        return $this->hasOne(Person::className(), ['person_id' => 'person_id']);
    }

    /**
     * 获取该申请，当前需要审批的信息
     * @return array|null|ApprovalLog
     */
    public function getNowApproval()
    {
        return $this->hasOne(ApprovalLog::className(), ['apply_id' => 'apply_id'])
            ->where(['is_to_me_now' => 1])
            ->one();
    }

    /**
     * 审批不通过的操作
     * @return bool
     */
    public function approvalFail()
    {
        $this->status = self::STATUS_FAIL;
        $this->next_des = '审批不通过，已终止';
        return $this->save();
    }

    /**
     * 审批通过，继续下一步审批
     * @param $person
     * @return bool
     */
    public function approvalPass($person)
    {
        $this->next_des = "等待{$person}审批";
        $this->status = self::STATUS_ING;
        return $this->save();
    }

    /**
     * 审批结束，待财务确认
     */
    public function approvalConfirm()
    {
        $this->next_des = '等待财务部门确认';
        $this->status = self::STATUS_CONFIRM;
        return $this->save();
    }

    /**
     * 审批通过，申请全部完成
     * @return bool
     */
    public function approvalComplete()
    {
        $this->next_des = '审批完成';
        $this->status = self::STATUS_OK;
        return $this->save();
    }

    /**
     * 报销单列表
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBaoXiaoList()
    {
        return $this->hasMany(BaoXiaoList::className(), ['apply_id' => 'apply_id']);
    }
}
