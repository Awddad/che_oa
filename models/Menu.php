<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_menu".
 *
 * @property string $id
 * @property string $slug
 * @property string $name
 */
class Menu extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_menu';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
            [['slug', 'name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'slug' => '目录别名',
            'name' => '目录名称',
        ];
    }
}
