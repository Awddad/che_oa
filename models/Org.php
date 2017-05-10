<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "oa_org".
 * 公司组织架构存储，数据同步于权限系统
 *
 * @property integer $org_id
 * @property string $org_name
 * @property integer $pid
 * @property string $org_short_name
 */
class Org extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_org';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['org_id', 'org_name', 'pid'], 'required'],
            [['org_id', 'pid'], 'integer'],
            [['org_name', 'org_short_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'org_id' => 'Org ID',
            'org_name' => '组织名称',
            'pid' => '父id',
            'org_short_name' => '组织简称',
        ];
    }

    /**
     * 获取员工的组织架构信息
     * @param null $data
     * @return array|null
     */
    public function getOrgInfo($data = null)
    {
        empty($data) ? $data = [$this->attributes] : null;
        $parent = $this->parent;
        if ($parent) {
            array_unshift($data, $parent->attributes);
            return $parent->getOrgInfo($data);
        }
        return $data;
    }

    /**
     * 获取员工组织架构信息的名称
     * @param bool $short
     * @param bool $hiddenTop
     * @return array
     */
    public function getOrgName($short = true, $hiddenTop = true)
    {
        $orgInfo = $this->getOrgInfo();
        if ($hiddenTop) {
            unset($orgInfo[0]);
        };

        if ($short) {
            return array_map(function ($value) {
                return (empty($value['org_short_name']) ? $value['org_name'] : $value['org_short_name']);
            }, $orgInfo);
        } else {
            return ArrayHelper::getColumn($orgInfo, 'org_name');
        }
    }

    /**
     * 获取上级组织信息
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(self::className(), ['org_id' => 'pid']);
    }
}