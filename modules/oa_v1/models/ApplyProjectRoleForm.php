<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/8/31
 * Time: 11:12
 */

namespace app\modules\oa_v1\models;


use app\models\ApplyProjectRole;
use app\models\Person;
use app\modules\oa_v1\logic\BaseLogic;
use yii\db\Exception;

class ApplyProjectRoleForm extends BaseForm
{
    /**
     * 是否需要财务确认
     * @var
     */
    public $cai_wu_need = 1;
    
    /**
     * 申请ID
     * @var
     */
    public $apply_id;
    
    /**
     * 商品上架
     * @var int
     */
    public $type = 16;
    
    /**
     * 附件
     * @var
     */
    public $files;
    
    /**
     * 描述
     * @var string
     */
    public $des = '';
    
    public $begin_at;
    
    public $end_at;
    
    public $project_id;
    public $project_name;
    public $role_id = 0;
    public $role_name = '';
    
    
    /**
     * 审批人
     * @var array
     */
    public $approval_persons = [];
    
    /**
     * 抄送人
     * @var array
     */
    public $copy_person = [];
    
    
    public function rules()
    {
        return [
            [['apply_id', 'des', 'begin_at', 'end_at','approval_persons', 'project_id', 'project_name'], 'required'],
            [['approval_persons', 'copy_person'], 'each', 'rule' => ['integer']],
            [['approval_persons', 'copy_person'], 'checkTotal'],
            ['des', 'string', 'max' => 1000],
            [['files','begin_at', 'end_at'], 'safe'],
            ['apply_id', 'checkOnly'],
        ];
    }
    
    
    public function attributeLabels()
    {
        return [
            'apply_id' => '申请单号',
            'approval_persons' => '审批人',
            'copy_person' => '审批人',
            'dsc' => '申请说明',
            'project_id' => '系统名称',
            'project_name' => '系统名称',
            'role_id' => '角色名称',
            'role_name' => '角色名称',
            'begin_at' => '开始时间',
            'end_at' => '结束时间',
            'des' => '申请说明',
            'files' => '附件',
        ];
    }
    
    /**
     * 保存
     *
     * @param Person $person
     *
     * @return string
     * @throws Exception
     */
    public function save($person)
    {
        $apply = $this->setApply($person);
        
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!$apply->save()) {
                throw new Exception('商品上架申请创建失败', $apply->errors);
            }
            $this->applyProjectRoleSave();
            $this->approvalPerson($apply);
            $this->copyPerson($apply);
            $transaction->commit();
            return $apply->apply_id;
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
    
    /**
     * 保存权限申请
     *
     * @return bool
     * @throws Exception
     */
    public function applyProjectRoleSave()
    {
        $applyProjectRole = new ApplyProjectRole();
        $data['ApplyProjectRole'] = [
            'apply_id' => $this->apply_id,
            'project_id' => $this->project_id,
            'project_name' => $this->project_name,
            'role_id' => $this->role_id,
            'role_name' => $this->role_name,
            'begin_at' => $this->begin_at,
            'end_at' => $this->end_at,
            'files' => json_encode($this->files),
            'des' => $this->des,
        ];
        if ($applyProjectRole->load($data) && $applyProjectRole->save()) {
            return true;
        } else {
            throw new Exception(BaseLogic::instance()->getFirstError($applyProjectRole->errors));
        }
    }
    
}