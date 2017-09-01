<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_project_role".
 *
 * @property string $apply_id
 * @property integer $project_id
 * @property string $project_name
 * @property integer $role_id
 * @property string $role_name
 * @property string $begin_at
 * @property string $end_at
 * @property string $des
 * @property string $files
 */
class ApplyProjectRole extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_project_role';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id', 'begin_at', 'end_at'], 'required'],
            [['project_id', 'role_id'], 'integer'],
            [['begin_at', 'end_at'], 'safe'],
            [['files'], 'string'],
            [['apply_id'], 'string', 'max' => 20],
            [['project_name', 'role_name'], 'string', 'max' => 64],
            [['des'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'apply_id' => 'Apply ID',
            'project_id' => '系统ID',
            'project_name' => '系统名称',
            'role_id' => '角色ID',
            'role_name' => '角色名称',
            'begin_at' => '开始时间',
            'end_at' => '结束时间',
            'des' => '申请说明',
            'files' => '附件',
        ];
    }
    
    /**
     * @param $applyId
     *
     * @return mixed|string
     */
    public static function getDes($applyId)
    {
        return static::find()->where(['apply_id'=>$applyId])->one()->des;
    }
}
