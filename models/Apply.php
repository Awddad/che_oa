<?php

namespace app\models;

use app\modules\oa_v1\logic\AssetLogic;
use app\modules\oa_v1\logic\BaseLogic;
use Yii;
use yii\db\Exception;

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
 * @property string $caiwu_refuse_reason
 * @property integer $apply_list_pdf
 * @property integer $org_id
 * @property integer $company_id
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
    const STATUS_OK = 99;//审核完成

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
            [['create_time', 'type', 'person_id', 'status', 'cai_wu_need', 'cai_wu_person_id', 'cai_wu_time', 'org_id', 'company_id'], 'integer'],
            [['apply_id'], 'string', 'max' => 20],
            [['title', 'person', 'approval_persons', 'copy_person', 'next_des', 'cai_wu_person', 'apply_list_pdf', 'caiwu_refuse_reason'], 'string', 'max' => 255],
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
        if($this->type == 3) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $this->status = self::STATUS_FAIL;
                $this->next_des = '审批不通过，已终止';
                if (!$this->save()) {
                    throw new Exception('审批不通过，操作失败');
                }
                $payBack = PayBack::findOne($this->apply_id);
                $applyIds = explode(',', $payBack->jie_kuan_ids);
                //改变借款单状态
                foreach ($applyIds as $apply_id) {
                    JieKuan::updateAll(['status' => 99], ['apply_id' => $apply_id]);
                }
                $transaction->commit();
                return true;
            } catch (Exception $e) {
                $transaction->rollBack();
                return true;
            }
        } elseif($this->type == 8) {
            AssetLogic::instance()->assetGetCancel($this);
        } elseif($this->type == 9) {
            AssetLogic::instance()->assetBackCancel($this);
        }
          
        $this->status = self::STATUS_FAIL;
        $this->next_des = '审批不通过，已终止';
        $person = Person::findOne($this->person_id);
        if($person->bqq_open_id) {
            $typeName = self::TYPE_ARRAY[$this->type];
            $data = [
                'tips_title' => 'OA -' .$typeName. '申请不通过',
                'tips_content' => '你发起的'. $typeName.'申请不通过，请在OA系统进行查看',
                'receivers' => $person->bqq_open_id,
            ];
            BaseLogic::instance()->sendQqMsg($data);
        }
        return $this->save();
        
    }

    /**
     * 审批通过，继续下一步审批
     * @param ApprovalLog $person
     * @return bool
     */
    public function approvalPass($person)
    {
        $this->next_des = '等待'.$person->approval_person.'审批';
        $this->status = self::STATUS_ING;
        $persons = Person::findOne($person->approval_person_id);
        if ($persons->bqq_open_id) {
            $typeName = self::TYPE_ARRAY[$this->type];
            $data = [
                'tips_title' => 'OA -' .$typeName. '申请',
                'tips_content' => '员工'.$this->person.'发起'. $typeName.'申请，请在OA系统进行审批处理',
                'receivers' => $persons->bqq_open_id,
            ];
            BaseLogic::instance()->sendQqMsg($data);
        }
        return $this->save();
    }

    /**
     * 审批结束，待财务确认
     */
    public function approvalConfirm()
    {
        $this->next_des = '等待财务部门确认';
        $this->status = self::STATUS_CONFIRM;
        /* 发送企业QQ消息 */
        $typeName = self::TYPE_ARRAY[$this->type];
        $person = Person::findOne($this->person_id);
        $data = [
            'tips_title' => 'OA - 付款确认',
            'tips_content' => '员工'.$this->person.'发起'. $typeName.'申请已通过，请在OA系统进行付款确认',
        ];
        $query = RoleOrgPermission::find()->select(['oa_role_org_permission.person_id'])->innerJoin(
            'oa_role','oa_role.id = oa_role_org_permission.role_id'
        )->where([
            'oa_role.slug' =>  'caiwu'
        ])->andWhere("FIND_IN_SET({$person->company_id}, oa_role_org_permission.company_ids)");
        foreach ($query->asArray()->all() as $v) {
            if ($person = Person::findOne($v['person_id'])) {
                $data['receivers'] = $person->bqq_open_id;
                BaseLogic::instance()->sendQqMsg($data);
            }
        }
        if($this->copy_person){
            $copyPersons = ApplyCopyPerson::find()->where(['apply_id' => $this->apply_id])->all();
            if(!empty($copyPersons)) {
                foreach ($copyPersons as $v) {
                    $copyPerson = Person::findOne($v->copy_person_id);
                    if ($copyPerson->bqq_open_id) {
                        $data = [
                            'tips_title' => 'OA - ' . $typeName . '申请完成',
                            'tips_content' => '员工' .$person->person_name . '发起的' . $typeName . '已完成，请在OA系统进行查看',
                            'receivers' => $copyPerson->bqq_open_id,
                        ];
                        BaseLogic::instance()->sendQqMsg($data);
                    }
                }
            }
        }
        return $this->save();
    }

    /**
     * 审批通过，申请全部完成
     * @return bool
     */
    public function approvalComplete()
    {
        $assetLogic = AssetLogic::instance();
        if($this->cai_wu_need == 1) {
            if ($this->type == 8) {
                $assetLogic->assetGetComplete($this);
            }
            if ($this->type == 9) {
                $assetLogic->assetBackComplete($this);
            }
        }
        $this->next_des = '审批完成';
        $this->status = self::STATUS_OK;
        /* 发送企业QQ消息 */
        $person = Person::findOne($this->person_id);
        $typeName = self::TYPE_ARRAY[$this->type];
        if ($person->bqq_open_id) {
            $data = [
                'tips_title' => 'OA - ' .$typeName. '申请完成',
                'tips_content' => '你发起的'. $typeName.'已完成，请在OA系统进行查看',
                'receivers' => $person->bqq_open_id,
            ];
            BaseLogic::instance()->sendQqMsg($data);
        }
        // 发送给抄送人
        if($this->cai_wu_need == 1) {
            if ($this->copy_person) {
                $copyPersons = ApplyCopyPerson::find()->where(['apply_id' => $this->apply_id])->all();
                if (!empty($copyPersons)) {
                    foreach ($copyPersons as $v) {
                        $copyPerson = Person::findOne($v->copy_person_id);
                        if ($copyPerson->bqq_open_id) {
                            $data = [
                                'tips_title' => 'OA - ' . $typeName . '申请完成',
                                'tips_content' => '员工' . $person->person_name . '发起的' . $typeName . '已完成，请在OA系统进行查看',
                                'receivers' => $copyPerson->bqq_open_id,
                            ];
                            BaseLogic::instance()->sendQqMsg($data);
                        }
                    }
                }
            }
        }
        /* end */
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
    
    /**
     * 获取请购
     * @return \yii\db\ActiveQuery
     */
    public function getApplyBuy()
    {
        return $this->hasOne(ApplyBuy::className(), ['apply_id' => 'apply_id']);
    }
    
    /**
     * 获取付款申请
     * @return \yii\db\ActiveQuery
     */
    public function getApplyPay()
    {
        return $this->hasOne(ApplyPay::className(), ['apply_id' => 'apply_id']);
    }
    
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getApplyDemand()
    {
        return $this->hasOne(ApplyDemand::className(), ['apply_id' => 'apply_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getApplyUseChapter()
    {
        return $this->hasOne(ApplyUseChapter::className(), ['apply_id' => 'apply_id']);
    }
    
    /**
     * 转正申请
     * @return \yii\db\ActiveQuery
     */
    public function getApplyPositive()
    {
    	return $this->hasOne(ApplyPositive::className(), ['apply_id' => 'apply_id']);
    }
    
    /**
     * 调职申请
     * @return \yii\db\ActiveQuery
     */
    public function getApplyTransfer()
    {
    	return $this->hasOne(ApplyTransfer::className(), ['apply_id' => 'apply_id']);
    }
    
    /**
     * 离职申请
     * @return \yii\db\ActiveQuery
     */
    public function getApplyLeave()
    {
        return $this->hasOne(ApplyLeave::className(), ['apply_id' => 'apply_id']);
    }
    
    /**
     * 开店申请
     * @return \yii\db\ActiveQuery
     */
    public function getApplyOpen()
    {
        return $this->hasOne(ApplyOpen::className(), ['apply_id' => 'apply_id']);
    }
    
    /**
     * 固定资产领取
     * @return \yii\db\ActiveQuery
     */
    public function getAssetGet()
    {
        return $this->hasOne(AssetGet::className(), ['apply_id' => 'apply_id']);
    }
    
    /**
     * 固定资产归还
     * @return \yii\db\ActiveQuery
     */
    public function getAssetBack()
    {
        return $this->hasOne(AssetBack::className(), ['apply_id' => 'apply_id']);
    }
    
    /**
     * 商品上架
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGoodsUp()
    {
        return $this->hasOne(GoodsUp::className(), ['apply_id' => 'apply_id']);
    }
    
    /**
     * 出差申请
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTravel()
    {
        return $this->hasOne(ApplyTravel::className(), ['apply_id' => 'apply_id']);
    }
    
    /**
     * @var array
     */
    const TYPE_ARRAY = [
        1 => '报销',
        2 => '备用金',
        3 => '还款',
        4 => '付款',
        5 => '请购',
        6 => '需求单',
        7 => '用章',
        8 => '固定资产领用',
        9 => '固定资产归还',
        10 => '转正',
        11 => '离职',
        12 => '调职',
        13 => '开店',
        14 => '商品上架',
        15 => '出差',
    ];
}
