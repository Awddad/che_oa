<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/6/14
 * Time: 14:52
 */

namespace app\modules\oa_v1\models;

use app\models\Apply;
use app\models\ApplyUseChapter;
use app\models\Org;
use app\models\Person;
use app\modules\oa_v1\logic\PersonLogic;
use Yii;
use yii\db\Exception;


/**
 * 用章申请
 *
 * Class ApplyUseChapterForm
 * @package app\modules\oa_v1\models
 */
class ApplyUseChapterForm extends BaseForm
{
    /**
     * 申请ID
     * @var
     */
    public $apply_id;
    
    /**
     *
     * @var int
     */
    public $type = 7;
    
    public $chapter_type;
    
    /**
     * @var 用途
     */
    public $use_type;
    
    public $name;
    
    public $name_path;
    
    public $files = '';
    
    public $des = '';
    
    /**
     * 审批人
     * @var array
     */
    public $approval_persons = [];
    
    /**
     * 抄送人
     * @var array
     */
    public $copy_person = [];
    
    
    public function rules()
    {
        return [
            [
                [ 'approval_persons', 'apply_id', 'chapter_type', 'use_type', 'name', 'name_path', 'files', 'des'],
                'required',
                'message' => '缺少必填字段'
            ],
            [
                ['approval_persons', 'copy_person'],
                'each',
                'rule' => ['integer']
            ],
            [
                ['approval_persons', 'copy_person'], 'checkTotal'
            ],
            ['files', 'safe'],
            ['des', 'string'],
            ['apply_id', 'checkOnly'],
            ['chapter_type', 'in', 'range' => [1,2,3,4,5]]
        ];
    }
    
    /**
     * @param Person $user
     * @return Apply
     * @throws \Exception
     */
    public function save($user)
    {
        $apply = $this->setApply($user);
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$apply->save()) {
                throw new Exception('付款申请单创建失败');
            }
            $this->saveApplyUseChapter();
            $this->approvalPerson($apply);
            $this->copyPerson($apply);
            $transaction->commit();
            return $apply;
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
    
    /**
     *
     * 保存需求单
     *
     * @return ApplyUseChapter
     * @throws Exception
     */
    public function saveApplyUseChapter()
    {
        $model = new ApplyUseChapter();
        $model->apply_id = $this->apply_id;
        $model->files = $this->files ? json_encode($this->files): '';
        $model->chapter_type = $this->chapter_type;
        $model->use_type = $this->use_type;
        $model->name = $this->name;
        $model->name_path= $this->name_path;
        $model->des = $this->des;
        if (!$model->save()) {
            throw new Exception('需求单保存失败');
        }
        return $model;
    }
}