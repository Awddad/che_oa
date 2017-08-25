<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "oa_job".
 *
 * @property integer $id
 * @property string $name
 * @property string $short_name
 * @property integer $pid
 * @property string $des
 * @property integer $is_delete
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $deleted_at
 * @property integer $need_exam
 */
class Job extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_job';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'need_exam'], 'required'],
            [['id', 'pid', 'is_delete', 'created_at', 'updated_at', 'deleted_at', 'need_exam'], 'integer'],
            [['name'], 'string', 'max' => 20],
            [['short_name'], 'string', 'max' => 16],
            [['des'], 'string', 'max' => 255],
            [['pid'], 'default', 'value' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '职位名称',
            'short_name' => '职位简称',
            'pid' => '上级职位',
            'des' => '职位描述',
            'is_delete' => '是否删除 0：否    1：删除',
            'need_exam' => '是否需要考试',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'deleted_at' => '删除时间',
        ];
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className()
        ];
    }
}
