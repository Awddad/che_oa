<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_use_chapter".
 *
 * @property string $apply_id
 * @property integer $chapter_type
 * @property string $name
 * @property string $des
 * @property string $files
 */
class ApplyUseChapter extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_use_chapter';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id'], 'required'],
            [['chapter_type'], 'integer'],
            [['files'], 'string'],
            [['apply_id'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 128],
            [['des'], 'string', 'max' => 512],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'apply_id' => '申请ID',
            'chapter_type' => '印章类型1.公章 2.财务章 3.法人章 4.合同专用章 5.发票专用章',
            'name' => '印章名称',
            'des' => '事由',
            'files' => '附件',
        ];
    }
}
