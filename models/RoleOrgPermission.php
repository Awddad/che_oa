<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_role_org_permission".
 *
 * @property integer $person_id
 * @property integer $role_id
 * @property string $org_ids
 * @property string $company_ids
 */
class RoleOrgPermission extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_role_org_permission';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['person_id', 'role_id', 'org_ids'], 'required'],
            [['person_id', 'role_id'], 'integer'],
            [['org_ids', 'company_ids'], 'string'],
            [['person_id', 'role_id'], 'unique', 'targetAttribute' => ['person_id', 'role_id'], 'message' => 'The combination of 用户id and 角色id has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'person_id' => '用户id',
            'role_id' => '角色id',
            'org_ids' => '组织架构的id，多个逗号分隔',
        ];
    }
}
