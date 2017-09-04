<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_apply_positive".
 *
 * @property integer $id
 * @property string $apply_id
 * @property string $prosecution
 * @property string $summary
 * @property string $suggest
 * @property string $entry_time
 * @property integer $org_id
 * @property string $org
 * @property integer $profession_id
 * @property string $profession
 * @property string $positive_time
 * @property string $files
 * @property integer $created_at
 */
class ApplyPositive extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_apply_positive';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['org_id', 'profession_id', 'created_at'], 'integer'],
            [['files'], 'string'],
            [['apply_id', 'profession'], 'string', 'max' => 20],
            [['prosecution', 'summary', 'suggest'], 'string', 'max' => 1024],
            [['entry_time', 'positive_time'], 'string', 'max' => 25],
            [['org'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'apply_id' => 'Apply ID',
            'prosecution' => 'Prosecution',
            'summary' => 'Summary',
            'suggest' => 'Suggest',
            'entry_time' => 'Entry Time',
            'org_id' => 'Org ID',
            'org' => 'Org',
            'profession_id' => 'Profession ID',
            'profession' => 'Profession',
            'positive_time' => 'Positive Time',
            'files' => 'Files',
            'created_at' => 'Created At',
        ];
    }
    
    public function getDesInfo()
    {
        return $this->summary;
    }
}
