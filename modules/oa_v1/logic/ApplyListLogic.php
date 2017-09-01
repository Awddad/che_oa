<?php
/**
 * Created by PhpStorm.
 * User: xiongjun
 * Date: 2017/8/31
 * Time: 18:00
 */

namespace app\modules\oa_v1\logic;


use app\models\Apply;
use app\models\ApplyCopyPerson;
use app\models\ApprovalLog;
use app\models\Person;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * 审批列表逻辑
 *
 * Class ApplyListLogic
 * @package app\modules\oa_v1\logic
 */
class ApplyListLogic extends BaseLogic
{
    /**
     * @var Person $person
     */
    public $person;
    
    public function getList()
    {
        // 接口类型 1：待我审批 2：我已审批 3：我发起的 4：抄送给我的
        $type = \Yii::$app->request->get('type');
        
        if ($type == 1) {
            $data = $this->toMeList();
        } elseif($type == 2) {
            $data = $this->approvalList();
        } elseif($type == 3) {
            $data = $this->applyList();
        } else {
            $data = $this->copyToMelList();
        }
        
        return $data;
    }
    
    /**
     * 待我审批
     *
     *
     * @return array
     */
    public function toMeList()
    {
        $query = ApprovalLog::find()->alias('b');
        $query->innerJoin('oa_apply a', 'a.apply_id = b.apply_id');
        $query->andWhere(['b.is_to_me_now' => 1]);
        $query->andWhere(['in', 'a.status', [1, 11]]);
    
        $this->getQueryParam($query);
    
        //排序
        $sort = \Yii::$app->request->get('sort');
        if ($sort == 'asc') {
            $orderBy['a.create_time'] =  SORT_ASC;
        } else {
            $orderBy['a.create_time'] = SORT_DESC;
        }
    
        $query->andWhere(['b.approval_person_id' => $this->person->person_id]);
    
        $pageSize = ArrayHelper::getValue(\Yii::$app->request->get(), 'page_size', 20);
    
        $pagination = new Pagination([
            'defaultPageSize' => $pageSize,
            'totalCount' => $query->count(),
        ]);
        $query->orderBy($orderBy)->offset($pagination->offset)->limit($pagination->limit);
    
        $data = [];
        /**
         * @var ApprovalLog $approvalLog
         */
        foreach ($query->all() as $k => $approvalLog) {
            /**
             * @var Apply $apply
             */
            $apply = $approvalLog->apply;
            $dataInfo = $this->getDefaultList($apply);
            $dataInfo['id'] = $pagination->pageSize * $pagination->getPage() + $k + 1;
            $dataInfo['end_at'] = date('Y-m-d H:i',$apply->end_time);
            $dataInfo['next_des'] = $apply->next_des;
            $data[$k] = $dataInfo;
        }
        return [
            'res' => $data,
            'page' => $this->pageFix($pagination)
        ];
    }
    
    /**
     * 我已审批
     *
     *
     * @return array
     */
    public function approvalList()
    {
        $query = ApprovalLog::find()->alias('b');
        $query->innerJoin('oa_apply a', 'a.apply_id = b.apply_id');
    
        $this->getQueryParam($query);
    
        //排序
        $sort = \Yii::$app->request->get('sort');
        if ($sort == 'asc') {
            $orderBy['a.create_time'] =  SORT_ASC;
        } else {
            $orderBy['a.create_time'] = SORT_DESC;
        }
    
        $query->andWhere(['b.approval_person_id' => $this->person->person_id])->andWhere(['>', 'b.result', 0]);
    
        $pageSize = ArrayHelper::getValue(\Yii::$app->request->get(), 'page_size', 20);
    
        $pagination = new Pagination([
            'defaultPageSize' => $pageSize,
            'totalCount' => $query->count(),
        ]);
        $query->orderBy($orderBy)->offset($pagination->offset)->limit($pagination->limit);
    
        $data = [];
        /**
         * @var ApprovalLog $approvalLog
         */
        foreach ($query->all() as $k => $approvalLog) {
            /**
             * @var Apply $apply
             */
            $apply = $approvalLog->apply;
            if ($apply->status == 2) {
                if($approvalLog->result == 2)  {
                    $des = $apply->next_des. '<br>原因:' .$approvalLog->des;
                } else {
                    $log = ApprovalLog::find()->where([
                        'apply_id' => $approvalLog->apply_id,
                        'result' => 2
                    ])->one();
                    $des = $apply->next_des. '<br>原因:' .$log->des;
                }
                
            } elseif ($apply->status == 5 || $apply->status == 6 || $apply->status == 7) {
                $des = $apply->next_des.'<br>原因:' .$apply->caiwu_refuse_reason;
            } else {
                $des = $apply->next_des;
            }
            $dataInfo = $this->getDefaultList($apply);
            $dataInfo['id'] = $pagination->pageSize * $pagination->getPage() + $k + 1;
            $dataInfo['end_at'] = date('Y-m-d H:i',$apply->end_time);
            $dataInfo['next_des'] = $des;
            $data[$k] = $dataInfo;
        }
        return [
            'res' => $data,
            'page' => $this->pageFix($pagination)
        ];
    }
    
    /**
     * 我发起的审批
     *
     * @return array
     */
    public function applyList()
    {
        $query = Apply::find()->alias('a');
    
        $query->where(['person_id' => $this->person->person_id]);
    
        $this->getQueryParam($query);
    
        //排序
        $sort = \Yii::$app->request->get('sort');
        if ($sort == 'asc') {
            $orderBy['a.create_time'] =  SORT_ASC;
        } else {
            $orderBy['a.create_time'] = SORT_DESC;
        }
        $pageSize = ArrayHelper::getValue(\Yii::$app->request->get(), 'page_size', 20);
    
        $pagination = new Pagination([
            'defaultPageSize' => $pageSize,
            'totalCount' => $query->count(),
        ]);
        $query->orderBy($orderBy)->offset($pagination->offset)->limit($pagination->limit);
        
        
        $data = [];
        /**
         * @var Apply $apply
         */
        foreach ($query->all() as $k => $apply) {
            if ($apply->status == 2) {
                    $log = ApprovalLog::find()->where([
                        'apply_id' => $apply->apply_id,
                        'result' => 2
                    ])->one();
                    $des = $apply->next_des. '<br>原因:' .$log->des;
        
            } elseif ($apply->status == 5 || $apply->status == 6 || $apply->status == 7) {
                $des = $apply->next_des.'<br>原因:' .$apply->caiwu_refuse_reason;
            } else {
                $des = $apply->next_des;
            }
            $dataInfo = $this->getDefaultList($apply);
            $dataInfo['id'] = $pagination->pageSize * $pagination->getPage() + $k + 1;
            $dataInfo['end_at'] = date('Y-m-d H:i',$apply->end_time);
            $dataInfo['next_des'] = $des;
            $data[$k] = $dataInfo;
        }
        return [
            'res' => $data,
            'page' => $this->pageFix($pagination)
        ];
    }
    
    
    /**
     * 抄送给我的审批
     *
     *
     * @return array
     */
    public function copyToMelList()
    {
        $query = ApplyCopyPerson::find()->alias('b');
        $query->innerJoin('oa_apply a', 'a.apply_id = b.apply_id');
    
        $this->getQueryParam($query);
    
        //排序
        $sort = \Yii::$app->request->get('sort');
        if ($sort == 'asc') {
            $orderBy['a.create_time'] =  SORT_ASC;
        } else {
            $orderBy['a.create_time'] = SORT_DESC;
        }
    
    
        $pageSize = ArrayHelper::getValue(\Yii::$app->request->get(), 'page_size', 20);
    
        $pagination = new Pagination([
            'defaultPageSize' => $pageSize,
            'totalCount' => $query->count(),
        ]);
        $query->orderBy($orderBy)->offset($pagination->offset)->limit($pagination->limit);
        
        $query->andWhere(['b.copy_person_id' => $this->person->person_id])->andWhere(['in', 'a.status', [4, 5, 99]]);
        
        $isRead = \Yii::$app->request->get('is_read');
        if ($isRead){
            $query->andWhere(['b.is_read' => $isRead]);
        }
        $orderBy['b.pass_at'] = SORT_DESC;
        $data = [];
        /**
         * @var ApplyCopyPerson $copy
         */
        foreach ($query->all() as $k => $copy) {
            /**
             * @var Apply $apply
             */
            $apply = $copy->apply;
            if ($apply->status == 5 || $apply->status == 6 || $apply->status == 7) {
                $des = $apply->next_des.'<br>原因:' .$apply->caiwu_refuse_reason;
            } else {
                $des = $apply->next_des;
            }
            $dataInfo = $this->getDefaultList($apply);
            $dataInfo['id'] = $pagination->pageSize * $pagination->getPage() + $k + 1;
            $dataInfo['is_read'] = $copy->is_read;
            $dataInfo['end_at'] = date('Y-m-d H:i',$apply->end_time);
            $dataInfo['next_des'] = $des;
            $data[$k] = $dataInfo;
        }
        return [
            'res' => $data,
            'page' => $this->pageFix($pagination)
        ];
    }
    
    /**
     * @param Apply $apply
     *
     * @return array
     */
    private function getDefaultList($apply)
    {
        return [
            'apply_id' => $apply->apply_id, // 审批单编号
            'date' => date('Y-m-d H:i', $apply->create_time), // 创建时间
            'title' => $apply->title, // 标题
            'status' => $apply->status, // 状态
            'type' => $apply->type, // 类型
            'des' => $apply->info->desInfo,
        ];
    }
    
    /**
     * @param ActiveQuery $query
     *
     * @return mixed
     */
    public function getQueryParam($query)
    {
        //状态
        if ($status = \Yii::$app->request->get('status')) {
        
            $arr_status = [];
            foreach ($status as $v) {
                switch ($v) {
                    case 1://审核中
                        array_push($arr_status, 1, 11);
                        break;
                    case 2://财务确认中
                        array_push($arr_status, 4);
                        break;
                    case 3://撤销
                        array_push($arr_status, 3);
                        break;
                    case 4://审核不通过
                        array_push($arr_status, 2);
                        break;
                    case 5://完成
                        array_push($arr_status, 99);
                        break;
                    case 6://财务驳回
                        array_push($arr_status, 5);
                        break;
                    case 7://付款失败
                        array_push($arr_status, 6, 7);
                        break;
                    default:
                        break;
                }
            }
            $query->andWhere(['in', 'a.status', $arr_status]);
        }
    
        //类型
        if ($at = \Yii::$app->request->get('at')) {
            $applyType = (array)@$at;
        } else {
            $applyType = null;
        }
        if ($applyType) {
            $query->andWhere(['in', 'a.type', $applyType]);
        }
    
    
        //关键词搜索
        $keywords = trim(\Yii::$app->request->get('keywords'));
        if ($keywords) {
            $query->andWhere("instr(CONCAT(a.apply_id,a.title,a.person,a.approval_persons,a.copy_person),'{$keywords}') > 0 ");
        }
        return $query;
    }
}