<?php
/**
 * Created by PhpStorm.
 * User: YJR
 * Date: 2017/8/17
 * Time: 16:18
 */

namespace app\commands;


use app\models\ApprovalConfig;
use app\models\Org;
use app\models\Person;
use app\modules\oa_v1\logic\OrgLogic;
use moonland\phpexcel\Excel;
use yii\console\Controller;
use yii\db\Exception;

class ApprovalConfigController extends Controller
{
    public $persons;

    public function actionIndex()
    {
        $data = Excel::import(\Yii::$app->basePath . '/goodsUp.xlsx', [
            'setFirstRecordAsKeys' => false,
            'setIndexSheetByName' => true,
        ]);
        array_shift($data);
        $persons_tmp = Person::find()->select('person_id,person_name')->where(['is_delete' => 0])->all();
        foreach ($persons_tmp as $v) {
            $this->persons[$v->person_name] = $v->person_id;
        }
        unset($persons_tmp);

        $error = 0;
        $model = new ApprovalConfig();
        foreach ($data as $v) {
            $org = Org::find()->where(['org_name' => $v['A']])->one();
            $_model = clone $model;
            $_model->apply_type = 14;
            $_model->apply_name = '商品上架';
            $_model->org_id = $org->org_id;
            $_model->org_name = OrgLogic::instance()->getOrgName($org->org_id);
            $_model->approval = $this->setConfig($v);
            $_model->type = 0;
            try {
                $_model->save();
            } catch (\Exception $e) {
                $error += 1;
            }
        }
        echo "失败{$error}条";die();
    }

    private function setConfig($row)
    {
        $arr = [
            $this->persons[trim($row['B'])],
            $this->persons[trim($row['C'])],
            $this->persons[trim($row['D'])],
            $this->persons[trim($row['E'])],
        ];
        return '{"0":'.json_encode($arr).'}';
    }
}