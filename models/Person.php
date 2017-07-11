<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_person".
 *
 * @property integer $person_id
 * @property string $person_name
 * @property integer $org_id
 * @property string $org_name
 * @property string $org_full_name
 * @property integer $is_delete
 * @property string $profession
 * @property string $email
 * @property string $phone
 * @property string $access_token
 * @property integer $last_login_time
 * @property string $bqq_open_id
 * @property string $role_ids
 * @property integer $company_id
 */
class Person extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_person';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['person_id', 'org_id'], 'required'],
            [['person_id', 'org_id', 'is_delete', 'last_login_time', 'company_id'], 'integer'],
            [['person_name', 'org_name', 'org_full_name', 'email', 'bqq_open_id', 'role_ids'], 'string', 'max' => 255],
            [['profession'], 'string', 'max' => 4],
            [['phone'], 'string', 'max' => 11],
            [['access_token'], 'string', 'max' => 1000],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'person_id' => '员工id',
            'person_name' => '员工姓名',
            'org_id' => '员工所在的组织id',
            'org_name' => '员工所在的组织名称',
            'org_full_name' => '用户的组织名称全称（从最高层往下显示）',
            'is_delete' => '员工是否被删除： 
0 - 正常 
1 - 删除',
            'profession' => '员工职位',
            'email' => '员工邮箱',
            'phone' => '员工手机号',
            'access_token' => '员工登录的token',
            'last_login_time' => '员工最近一次登录时间',
            'bqq_open_id' => '员工企业QQ的信息',
            'role_ids' => '员工角色id',
            'company_id' => '公司ID',
        ];
    }

    /**
     * 获取该员工的组织信息
     * @return \yii\db\ActiveQuery
     */
    public function getOrg()
    {
        return $this->hasOne(Org::className(), ['org_id' => 'org_id']);
    }
}
