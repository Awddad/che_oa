<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/20
 * Time: 09:56
 */

namespace app\modules\oa_v1\models;


use app\models\AssetGet;
use app\models\Person;
use app\modules\oa_v1\logic\AssetLogic;
use yii\db\Exception;


/**
 * 资产获取
 *
 * Class AssetGetForm
 * @package app\modules\oa_v1\models
 */
class AssetGetForm extends BaseForm
{
    public $get_person;
    
    public $des = '';
    
    public $files;
    
    public $type = 8;
    
    public $apply_id;
    
    public $asset_ids = [];
    
    /**
     * 表单验证
     */
    public function rules()
    {
        return [
            [
                ['apply_id', 'approval_persons', 'asset_ids'], 'required'
            ],
            [
                ['approval_persons', 'copy_person', 'asset_ids'],
                'each',
                'rule' => ['integer']
            ],
            [
                ['approval_persons', 'copy_person'], 'checkTotal'
            ],
            [['des'], 'string'],
            ['files', 'safe'],
            ['apply_id', 'checkOnly'],
        ];
    }
    
    /**
     * @param Person $person
     * @return mixed
     * @throws Exception
     */
    public function save($person)
    {
        $apply = $this->setApply($person);
        
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!$apply->save()) {
                throw new Exception('付款申请单创建失败', $apply->errors);
            }
            $this->saveAssetGet($person);
            $this->approvalPerson($apply);
            $this->copyPerson($apply);
            $this->saveAssetGetList($person);
            AssetLogic::instance()->assetGet($apply);
            $transaction->commit();
            $this->afterApplySave($apply);
            return $apply;
        } catch (Exception $e) {
            $transaction->rollBack();
            $this->addError('',$e->getMessage());
            return false;
        }
    }
    
    /**
     * 资产领用
     *
     * @param Person $person
     * @return AssetGet
     * @throws Exception
     */
    public function saveAssetGet($person)
    {
        $model = new AssetGet();
        $model->apply_id = $this->apply_id;
        $model->des = $this->des;
        $model->get_person = $person['person_id'];
        $model->files = $this->files ? json_encode($this->files): '';
        if (!$model->save()) {
            throw new Exception('固定资产领用单创建失败', $model->errors);
        }
        return $model;
    }
    
    /**
     * 资产领用列表
     * @param Person $person
     *
     * @throws Exception
     */
    public function saveAssetGetList($person)
    {
        $data = [];
        foreach ($this->asset_ids as $v) {
            $data[] = [
                $this->apply_id,
                $person->person_id,
                $v,
                1,
                time()
            ];
        }
        $n = \Yii::$app->db->createCommand()->batchInsert('oa_asset_get_list', [
            'apply_id', 'person_id', 'asset_id', 'status', 'created_at'
        ], $data)->execute();
        if(!$n) {
            throw new Exception('固定资产领用单创建失败!');
        }
    }
}