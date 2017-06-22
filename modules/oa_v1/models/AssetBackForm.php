<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/20
 * Time: 10:58
 */

namespace app\modules\oa_v1\models;

use app\models\Apply;
use app\models\AssetBack;
use app\models\AssetGetList;
use app\models\User;
use app\modules\oa_v1\logic\AssetLogic;
use app\modules\oa_v1\logic\PersonLogic;
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
            [['des', 'files'], 'string'],
            [
                ['approval_persons', 'copy_person', 'asset_back_ids'],
                'each',
                'rule' => ['integer']
            ],
            [
                ['approval_persons', 'copy_person'], 'checkTotal'
            ],
            [['des'], 'string'],
            ['files', 'each'],
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
                throw new Exception('付款申请单创建失败');
            }
            $this->saveAssetGet();
            $this->approvalPerson($apply);
            $this->copyPerson($apply);
            $this->saveAssetGetList();
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
     * @return AssetBack
     * @throws Exception
     */
    public function saveAssetGet()
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
        $result = AssetGetList::updateAll(['status' => 4], ['in', 'id', $this->asset_back_ids]);
        if(!$result) {
            throw new Exception('固定资产归还单创建失败');
        }
        return true;
    }
}