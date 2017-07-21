<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/9
 * Time: 15:40
 */

namespace app\modules\oa_v1\models;


use app\logic\server\ThirdServer;
use app\models\Apply;
use app\models\BaoXiao;
use app\models\BaoXiaoList;
use app\models\CaiWuFuKuan;
use app\models\JieKuan;
use app\models\Org;
use app\models\Person;
use yii\db\Exception;
use yii\web\UploadedFile;

/**
 * 付款确认表单
 *
 * Class PayConfirmForm
 * @package app\modules\oa_v1\models
 */
class PayConfirmForm extends CaiWuFuKuan
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'apply_id', 'org_id', 'bank_card_id', 'fu_kuan_id', 'fu_kuan_time', 'type',
                    'bank_name', 'account_id'
                ],
                'required'
            ],
            [['org_id', 'type', 'fu_kuan_time', 'create_time', 'account_id'], 'integer'],
            [['tips', 'pics'], 'string'],
            [['apply_id'], 'string', 'max' => 20],
            [['org_name', 'bank_name', 'bank_name_des', 'fu_kuan_id'], 'string', 'max' => 255],
            ['bank_name_des', 'default', 'value' => ''],
            [['bank_card_id'], 'string', 'max' => 50],
        ];
    }
    
    
    /**
     * 保存文件
     *
     * @param $name
     *
     * @return array |boolean
     */
    public function saveUploadFile($name)
    {
        $files = UploadedFile::getInstancesByName($name);
        if (empty($files)) {
            return false;
        }
        $basePath = \Yii::$app->basePath . '/web';
        $filePath = '/upload/payconfirm/' . date('Y-m-d') . '/';
        $rootPath = $basePath . $filePath;
        $data = [];
        foreach ($files as $file) {
            $ext = $file->getExtension();
            if (!in_array($ext, ['jpg', 'gif', 'png'])) {
                $this->addError('格式错误');
                
                return false;
            }
            $randName = $file->name;
            if (!file_exists($rootPath)) {
                mkdir($rootPath, 0777, true);
            }
            $fileName = $rootPath . $randName;
            $file->saveAs($fileName);
            $data[] = 'http://' . $_SERVER['HTTP_HOST'] . $filePath . $randName;
        }
        
        return implode(",", $data);
    }
    
    /**
     * @param Person $person
     *
     * @return bool
     * @throws Exception
     */
    public function saveConfirm($person)
    {
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();
        $apply = Apply::findOne($this->apply_id);
        $apply->status = 99; //订单完成
        $apply->next_des = '完成';
        $apply->cai_wu_person_id = $person->person_id;
        $apply->cai_wu_time = time();
        $apply->cai_wu_person = $person->person_name;
        $list = \Yii::$app->request->post('baoxiao_list');
        if ($apply->type == 1 && empty($list)) {
            $this->addError('apply_id', '缺少必要参数');
            
            return false;
        }
        try {
            //js 和 PHP 时间戳相差1000
            $this->fu_kuan_time = $this->fu_kuan_time / 1000;
            $this->org_name = Org::findOne($this->org_id)->org_name;
            $this->create_time = time();
            if (!$this->save()) {
                throw new Exception('确认失败', $this->errors);
            }
            $apply->save();
            // 更新报销列表报销类型
            if ($apply->type == 1) {
                foreach ($list as $v) {
                    BaoXiaoList::updateAll(['type' => $v['type']], ['id' => $v['id']]);
                }
            }
            //更新借款单状态
            if ($apply->type == 2) {
                JieKuan::updateAll([
                    'get_money_time' => time(),
                    'status' => 99
                ], ['apply_id' => $apply->apply_id]);
            }
            
            $transaction->commit();
        } catch (Exception $exception) {
            $transaction->rollBack();
            throw $exception;
        }
        
        if (\Yii::$app->request->post('create_cai_wu_log') == 1) {
            $param = [];
            $param['organization_id'] = $this->org_id;
            $param['account_id'] = $this->account_id;
            $param['tag_id'] = $this->type;
            $param['money'] = $this->getMoney($apply);
            $param['time'] = date('Y-m-d H:i:s', $this->fu_kuan_time);
            //$param['remark'] = $this->tips;
            
            if ($apply->type == 1) {
                $param['other_name'] = $apply->person;
                $param['other_card'] = $apply->expense->bank_card_id;
                $param['other_bank'] = $apply->expense->bank_name;
            } elseif ($apply->type == 2) {
                $param['other_name'] = $apply->person;
                $param['other_card'] = $apply->loan->bank_card_id;
                $param['other_bank'] = $apply->loan->bank_name;
                $param['remark'] = $apply->loan->des;
            } elseif ($apply->type == 4) {
                $param['other_name'] = $apply->applyPay->to_name;
                $param['other_card'] = $apply->applyPay->bank_card_id;
                $param['other_bank'] = $apply->applyPay->bank_name;
                $param['remark'] = $apply->applyPay->des;
            } else {
                $param['other_name'] = $apply->applyBuy->to_name;
                $param['other_card'] = $apply->applyBuy->bank_card_id;
                $param['other_bank'] = $apply->applyBuy->bank_name;
                $param['remark'] = $apply->applyBuy->des;
            }
            
            $param['trade_number'] = $this->fu_kuan_id;
            $param['order_number'] = $this->apply_id;
            //财务系统约定
            $param['order_type'] = 104;
            if ($apply->type == 1) {
                $flag = true;
                /**
                 * @var BaoXiaoList $v
                 */
                foreach ($apply->baoXiaoList as $v) {
                    $param['tag_id'] = $v->type;
                    $param['money'] = $v->money;
                    $param['remark'] = $v->des;
                    $rst = ThirdServer::instance([
                        'token' => \Yii::$app->params['cai_wu']['token'],
                        'baseUrl' => \Yii::$app->params['cai_wu']['baseUrl']
                    ])->payment($param);
                    
                    if (!$rst['success'] == 1) {
                        $flag = false;
                    }
                }
                if ($flag) {
                    $this->is_told_cai_wu_success = 1;
                    $this->update();
                } else {
                    $this->is_told_cai_wu_success = 2;
                    $this->update();
                }
            } else {
                $rst = ThirdServer::instance([
                    'token' => \Yii::$app->params['cai_wu']['token'],
                    'baseUrl' => \Yii::$app->params['cai_wu']['baseUrl']
                ])->payment($param);
                if ($rst['success'] == 1) {
                    $this->is_told_cai_wu_success = 1;
                    $this->update();
                } elseif ($rst['success'] == 0) {
                    $this->is_told_cai_wu_success = 2;
                    $this->update();
                }
            }
        }
        
        return true;
    }
    
    /**
     * @param $apply
     *
     * @return mixed
     */
    public function getMoney($apply)
    {
        switch ($apply->type) {
            case 2:
                $money = $apply->loan->money;
                break;
            case 4:
                $money = $apply->applyPay->money;
                break;
            case 5:
                $money = $apply->applyBuy->money;
                break;
            default:
                $money = $apply->expense->money;
                break;
        }
        
        return $money;
    }
}