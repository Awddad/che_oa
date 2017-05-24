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
use app\models\CaiWuFuKuan;
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
                    'bank_name'
                ],
                'required'
            ],
            [['org_id', 'type', 'fu_kuan_time', 'create_time'], 'integer'],
            [['tips'], 'string'],
            [['apply_id'], 'string', 'max' => 20],
            [['org_name', 'bank_name', 'bank_name_des', 'fu_kuan_id', 'pics'], 'string', 'max' => 255],
            [['bank_card_id'], 'string', 'max' => 25],
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

    public function saveConfirm()
    {
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();
        try{
            $this->org_name = Org::findOne($this->org_id)->org_name;
            $this->create_time = time();
            if (!$this->save()) {
                new Exception('确认失败', $this->errors);
            }
            $apply = Apply::findOne($this->apply_id);
            $apply->status = 99; //订单完成
            $apply->save();
            $person = Person::findOne($apply->person_id);
            $param = [];
            $param['organization_id'] = $person->org_id;
            $param['account_id'] = $person->person_id;
            $param['tag_id'] = $this->type;
            $param['money'] = $this->getMoney($apply);
            $param['time'] = date('Y-m-d h:i:s', $this->fu_kuan_time);
            $param['remark'] = $this->tips;

            if($apply->type == 1) {
                $param['other_name'] = $person->person_name;
                $param['other_card'] = $apply->expense->bank_card_id;
                $param['other_bank'] = $apply->expense->bank_name;
            }

            if($apply->type == 2) {
                $param['other_name'] = $person->person_name;
                $param['other_card'] = $apply->loan->bank_card_id;
                $param['other_bank'] = $apply->loan->bank_name;
            }

            $param['trade_number'] = $this->fu_kuan_id;
            $param['order_number'] = $this->apply_id;
            $param['order_type'] = 1;
            $transaction->commit();
        } catch (Exception $exception) {
            $transaction->rollBack();
            throw $exception;
        }
        if($apply->type == 2) {
            $rst = ThirdServer::instance([
                'token' => \Yii::$app->params['cai_wu']['token'],
                'baseUrl' => \Yii::$app->params['cai_wu']['baseUrl']
            ])->payment($param);
            if($rst['success'] == 1) {
                $this->is_told_cai_wu_success = 1;
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
        return true;
    }

    /**
     * @param Apply $apply
     */
    public function getMoney($apply)
    {
        if($apply->type == 1) {
            $money = $apply->expense->money;
        }
        if($apply->type == 2){
            $money = $apply->loan->money;
        }
        if($apply->type == 3) {
            $money = $apply->payBack->money;
        }
        return $money;
    }
}