<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_tag_tree".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property string $name
 * @property integer $type
 * @property integer $level
 * @property integer $status
 */
class TagTree extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_tag_tree';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'parent_id', 'type', 'level', 'status'], 'integer'],
            [['name'], 'string', 'max' => 128],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => '父级id',
            'name' => '名称',
            'type' => '类型',
            'level' => '级别',
            'status' => '状态',
        ];
    }
}
