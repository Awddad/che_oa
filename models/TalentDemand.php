<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "oa_talent_demand".
 *
 * @property integer $id
 * @property integer $owner
 * @property integer $org_id
 * @property string $org_name
 * @property integer $profession_id
 * @property string $profession
 * @property integer $number
 * @property integer $sex
 * @property integer $edu_id
 * @property string $edu
 * @property string $work_time
 * @property string $des
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class TalentDemand extends \yii\db\ActiveRecord
{
    const STATUS = [
        0 => '未招聘',
        1 => '招聘中',
        2 => '已招聘',
        3 => '取消招聘'
    ];
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
        return 'oa_talent_demand';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['owner', 'org_id', 'profession_id', 'number', 'sex', 'edu_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['org_name'], 'string', 'max' => 50],
            [['profession'], 'string', 'max' => 20],
            [['edu', 'work_time'], 'string', 'max' => 10],
            [['des'], 'string', 'max' => 1024],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'owner' => '创建人',
            'org_id' => 'Org ID',
            'org_name' => '部门',
            'profession_id' => '职位ID',
            'profession' => '职位',
            'number' => '人数',
            'sex' => '性别',
            'edu_id' => '最高学历',
            'edu' => '最高学历',
            'work_time' => '工作年限',
            'des' => '描述',
            'status' => '状态',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
