<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/20
 * Time: 09:56
 */

namespace app\modules\oa_v1\models;


use app\models\Apply;
use app\models\AssetGet;
use app\models\Person;
use app\models\User;
use app\modules\oa_v1\logic\AssetLogic;
use app\modules\oa_v1\logic\PersonLogic;
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
     * @param User $user
     * @return mixed
     * @throws Exception
     */
    public function save($user)
    {
        $applyId = $this->apply_id;
        $pdfUrl = '';
        $nextName = PersonLogic::instance()->getPersonName($this->approval_persons[0]);
        
        $apply = new Apply();
        $apply->apply_id = $applyId;
        $apply->title = $this->createApplyTitle($user);
        $apply->create_time = $_SERVER['REQUEST_TIME'];
        $apply->type = $this->type;
        $apply->person_id = $user['person_id'];
        $apply->person = $user['person_name'];
        $apply->status = 1;
        $apply->next_des = '等待'.$nextName.'审批';
        $apply->approval_persons = $this->getPerson('approval_persons');
        $apply->copy_person = $this->getPerson('copy_person');
        $apply->apply_list_pdf = $pdfUrl;
        $apply->cai_wu_need = $this->cai_wu_need;
        $apply->org_id = $user['org_id'];
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!$apply->save()) {
                throw new Exception('付款申请单创建失败', $apply->errors);
            }
            $this->saveAssetGet($user);
            $this->approvalPerson($apply);
            $this->copyPerson($apply);
            $this->saveAssetGetList($user);
            AssetLogic::instance()->assetGet($apply);
            $transaction->commit();
            return $apply;
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
    
    /**
     * 资产领用
     *
     * @param $user
     * @return AssetGet
     * @throws Exception
     */
    public function saveAssetGet($user)
    {
        $model = new AssetGet();
        $model->apply_id = $this->apply_id;
        $model->des = $this->des;
        $model->get_person = $user['person_id'];
        $model->files = $this->files ? json_encode($this->files): '';
        if (!$model->save()) {
            throw new Exception('固定资产领用单创建失败', $model->errors);
        }
        return $model;
    }
    
    /**
     * 资产领用列表
     * @param Person $user
     *
     * @throws Exception
     */
    public function saveAssetGetList($user)
    {
        $data = [];
        foreach ($this->asset_ids as $v) {
            $data[] = [
                $this->apply_id,
                $user->person_id,
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