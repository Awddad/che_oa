<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_use_chapter".
 *
 * @property string $apply_id
 * @property integer $chapter_type
 * @property integer $use_type
 * @property string $name
 * @property string $name_path
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
            [['chapter_type', 'use_type'], 'integer'],
            [['files'], 'string'],
            [['apply_id'], 'string', 'max' => 20],
            [['name', 'name_path'], 'string', 'max' => 128],
            [['des'], 'string', 'max' => 512],
        ];
    }
    
    const STATUS = [
        1 => '公章',
        2 => '财务章',
        3 => '法人章',
        4 => '合同专用章',
        5 => '发票专用章',
    ];
    
    /**
     * @var array
     */
    const USE_TYPE = [
        1 => '用章',
        2 => '借章'
    ];

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'apply_id' => '申请ID',
            'chapter_type' => '印章类型1.公章 2.财务章 3.法人章 4.合同专用章 5.发票专用章',
            'use_type' => '用章类型 1:用章 2:借章',
            'name' => '印章名称',
            'des' => '事由',
            'files' => '附件',
        ];
    }
    
    /**
     * 获得用章说明
     *
     * @return string
     */
    public function getDesInfo()
    {
        return $this->des;
    }
}
