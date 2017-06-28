<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/9
 * Time: 15:40
 */

namespace app\modules\oa_v1\models;

use app\logic\MyTcPdf;
use app\models\BaoXiaoList;
use app\logic\server\ThirdServer;
use app\models\Apply;
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
     * @return array |boolean
     */
    public function saveUploadFile($name)
    {
        $files = UploadedFile::getInstancesByName($name);
        if(empty($files)) {
            return false;
        }
        $basePath = \Yii::$app->basePath.'/web';
        $filePath = '/upload/payconfirm/'.date('Y-m-d').'/';
        $rootPath = $basePath.$filePath;
        $data = [];
        foreach ($files as  $file) {
            $ext = $file->getExtension();
            if(!in_array($ext, ['jpg', 'gif', 'png'])) {
                $this->addError('格式错误');
                return false;
            }
            $randName = $file->name;
            if (!file_exists($rootPath)) {
                mkdir($rootPath, 0777, true);
            }
            $fileName = $rootPath.$randName;
            $file->saveAs($fileName);
            $data[] = 'http://'.$_SERVER['HTTP_HOST'].$filePath . $randName
            ;
        }
        return implode(",", $data);
    }
    
    /**
     * @param Person $person
     * @return bool
     * @throws Exception
     */
    public function saveConfirm($person)
    {
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();
        try{
            //js 和 PHP 时间戳相差1000
            $this->fu_kuan_time = $this->fu_kuan_time /1000;
            $this->org_name = Org::findOne($this->org_id)->org_name;
            $this->create_time = time();
            if (!$this->save()) {
                new Exception('确认失败', $this->errors);
            }
            $apply = Apply::findOne($this->apply_id);
            $apply->status = 99; //订单完成
            $apply->next_des = '完成';
            $apply->cai_wu_person_id = $person->person_id;
            $apply->cai_wu_time = time();
            $apply->cai_wu_person = $person->person_name;
            $apply->save();
            
            $transaction->commit();
        } catch (Exception $exception) {
            $transaction->rollBack();
            throw $exception;
        }
        if($apply->type == 1) {
            $arrInfo = [
                'apply_date' => date('Y年m月d日',$this -> create_time),
                'apply_id' => $apply -> apply_id,
                'org_full_name' => $person->org_full_name,
                'person' => $apply->person,
                'bank_name' => $apply->expense->bank_name.$apply->expense-> bank_name_des,
                'bank_card_id' => $apply->expense -> bank_card_id,
                'approval_person' =>$apply->approval_persons,//多个人、分隔
                'copy_person' => $apply->copy_person,//多个人、分隔
                'list' => [],
                'tips' => '--',
                'caiwu' => $person->person_name
            ];
            $baoXiaoList = BaoXiaoList::find()->where(['apply_id' => $apply->apply_id])->all();
            foreach($baoXiaoList as $v){
                $arrInfo['list'][] = [
                    'type_name' => $v['type_name'],
                    'money' => \Yii::$app->formatter->asCurrency($v['money']),
                    'detail' => @$v['des']
                ];
            }
            $root_path = \Yii::$app -> basePath.'/web'.$apply->apply_list_pdf;
            if(!file_exists($root_path)){
                unlink($root_path);
            }
            $myPdf = new MyTcPdf();
            $myPdf -> createBaoXiaoDanPdf($root_path, $arrInfo);
        } elseif($apply->type == 2) {
            $pdf = new  MyTcPdf();
            $root_path = \Yii::$app -> basePath.'/web'.$apply->apply_list_pdf;
            if(!file_exists($root_path)){
                unlink($root_path);
            }
            $pdf->createJieKuanDanPdf($root_path, [
                'apply_date' => date('Y年m月d日'),
                'apply_id' => $apply->apply_id,
                'org_full_name' => $person->org_full_name,
                'person' => $person->person_name,
                'bank_name' => $this->bank_name,
                'bank_card_id' => $this->bank_card_id,
                'money' => \Yii::$app->formatter->asCurrency($this->money),
                'detail' => $this->des,
                'tips' => $this->tips,
                'approval_person' =>$apply->approval_persons,//多个人、分隔
                'copy_person' => $apply->copy_person,//多个人、分隔
                'caiwu' => $person->person_name
            ]);
        }
        if(\Yii::$app->request->post('create_cai_wu_log')) {
            $param = [];
            $param['organization_id'] = $person->org_id;
            $param['account_id'] = $this->account_id;
            $param['tag_id'] = $this->type;
            $param['money'] = $this->getMoney($apply);
            $param['time'] = date('Y-m-d H:i:s', $this->fu_kuan_time);
            $param['remark'] = $this->tips;
    
            if($apply->type == 1) {
                $param['other_name'] = $person->person_name;
                $param['other_card'] = $apply->expense->bank_card_id;
                $param['other_bank'] = $apply->expense->bank_name;
            } elseif($apply->type == 2) {
                //借款单操作
                $jieKuan = JieKuan::findOne($this->apply_id);
                //借款成功
                $jieKuan->status = 99;
                $jieKuan->get_money_time = time();
                $jieKuan->save();
                $param['other_name'] = $apply->person;
                $param['other_card'] = $apply->loan->bank_card_id;
                $param['other_bank'] = $apply->loan->bank_name;
            } elseif($apply->type == 4){
                $param['other_name'] = $apply->person;
                $param['other_card'] = $apply->applyPay->bank_card_id;
                $param['other_bank'] = $apply->applyPay->bank_name;
            } else {
                $param['other_name'] = $apply->person;
                $param['other_card'] = $apply->applyBuy->bank_card_id;
                $param['other_bank'] = $apply->applyBuy->bank_name;
            }
    
            $param['trade_number'] = $this->fu_kuan_id;
            $param['order_number'] = $this->apply_id;
            //财务系统约定
            $param['order_type'] = 104;
            if($apply->type == 2) {
                $rst = ThirdServer::instance([
                    'token' => \Yii::$app->params['cai_wu']['token'],
                    'baseUrl' => \Yii::$app->params['cai_wu']['baseUrl']
                ])->payment($param);
                if($rst['success'] == 1) {
                    $this->is_told_cai_wu_success = 1;
                    $this->update();
                } elseif($rst['success'] == 0) {
                    $this->is_told_cai_wu_success = 2;
                    $this->update();
                }
            } else {
                $flag = true;
                foreach ($apply->baoXiaoList as $v) {
                    $param['tag_id'] = $v->type;  //没有 type字段呀
                    $param['money'] = $v->money;
                    $rst = ThirdServer::instance([
                        'token' => \Yii::$app->params['cai_wu']['token'],
                        'baseUrl' => \Yii::$app->params['cai_wu']['baseUrl']
                    ])->payment($param);
            
                    if(!$rst['success'] == 1) {
                        $flag = false;
                    }
                }
                if($flag) {
                    $this->is_told_cai_wu_success = 1;
                    $this->update();
                }
            }
        }
        return true;
    }

    /**
     * @param $apply
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