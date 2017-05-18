<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/5/9
 * Time: 15:41
 */

namespace app\modules\oa_v1\models;


use app\logic\server\ThirdServer;
use app\models\Apply;
use app\models\CaiWuShouKuan;
use app\models\Org;
use app\models\Person;
use yii\db\Exception;
use yii\web\UploadedFile;


/**
 * 还款确认
 *
 * Class BackConfirmForm
 * @package app\modules\oa_v1\models
 */
class BackConfirmForm extends CaiWuShouKuan
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'apply_id', 'org_id', 'bank_card_id', 'bank_name', 'shou_kuan_id',
                    'shou_kuan_time', 'create_cai_wu_log', 'type'
                ],
                'required'
            ],
            [['org_id', 'type', 'shou_kuan_time', 'create_cai_wu_log'], 'integer'],
            [['tips'], 'string'],
            [['apply_id', 'org_name', 'bank_name', 'bank_name_des', 'shou_kuan_id', 'pics'], 'string', 'max' => 255],
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
        $filePath = '/upload/payback/'.date('Y-m-d').'/';
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
            //$this->create_time = time();
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
            $param['time'] = date('Y-m-d h:i:s', $this->shou_kuan_time);;
            $param['remark'] = $this->tips;

            //收入 可为空
            //if($apply->type == 3) {
            //    $param['other_name'] = $person->person_name;
            //    $param['other_card'] = $apply->payBack->bank_card_id;
            //    $param['other_bank'] = $apply->payBack->bank_name;
            //}

            $param['trade_number'] = $this->shou_kuan_id;
            $param['order_number'] = $this->apply_id;
            $param['order_type'] = 1;
            $transaction->commit();
        } catch (Exception $exception) {
            $transaction->rollBack();
            throw $exception;
        }
        $rst = ThirdServer::instance([
            'token' => \Yii::$app->params['cai_wu']['token'],
            'baseUrl' => \Yii::$app->params['cai_wu']['baseUrl']
        ])->payment($param);
        if($rst['success'] == 1) {
            $this->is_told_cai_wu_success = 1;
            $this->update();
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
        } else if($apply->type == 2){
            $money = $apply->loan->money;
        } else {
            $money = $apply->payBack->money;
        }
        return $money;
    }
}