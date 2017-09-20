<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/20
 * Time: 10:58
 */

namespace app\modules\oa_v1\models;

use app\models\AssetBack;
use app\models\AssetGetList;
use app\models\Person;
use app\models\User;
use Yii;
use yii\db\Exception;


/**
 * 固定资产归还
 *
 * Class AssetBackForm
 * @package app\modules\oa_v1\models
 */
class AssetBackForm extends BaseForm
{
    /**
     * 申请ID
     * @var
     */
    public $apply_id;
    
    public $type = 9;
    
    public $get_person;
    
    public $des = '';
    
    public $files;
    
    public $asset_back_ids;
    
    /**
     * 表单验证
     */
    public function rules()
    {
        return [
            [
                [
                    'apply_id', 'get_person', 'approval_persons', 'asset_back_ids'
                ],
                'required'
            ],
            [
                ['approval_persons', 'copy_person', 'asset_back_ids'],
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
                throw new Exception('付款申请单创建失败');
            }
            $this->saveAssetBack();
            $this->approvalPerson($apply);
            $this->copyPerson($apply);
            $this->saveAssetGetList();
            $transaction->commit();
            $this->afterApplySave($apply);
            return $apply;
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
    
    /**
     * 资产领用
     *
     * @return AssetBack
     * @throws Exception
     */
    public function saveAssetBack()
    {
        $model = new AssetBack();
        $model->apply_id = $this->apply_id;
        $model->des = $this->des;
        $model->get_person = $this->get_person;
        $model->files = $this->files ? json_encode($this->files): '';
        $model->asset_list_ids = implode(',', $this->asset_back_ids);
        if (!$model->save()) {
            throw new Exception('固定资产归还单创建失败', $model->errors);
        }
        return $model;
    }
    
    /**
     * 更新资产领用列表，状态变为归还中
     *
     * @return bool
     * @throws Exception
     */
    public function saveAssetGetList()
    {
        $result = AssetGetList::updateAll(['status' => AssetGetList::STATUS_BACK_IN], [
            'in', 'id', $this->asset_back_ids
        ]);
        if(!$result) {
            throw new Exception('固定资产归还单创建失败');
        }
        return true;
    }
}