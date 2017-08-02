<?php
namespace app\modules\oa_v1\models;

use moonland\phpexcel\Excel;
use yii;
use app\modules\oa_v1\logic\SalaryLogic;
use yii\data\Pagination;
use app\modules\oa_v1\logic\BackLogic;
use yii\helpers\ArrayHelper;
use app\models\Salary;
use app\models\Employee;

class SalaryForm extends BaseForm
{
    const SCENARIO_IMPORT = 'import';//导入
    
    public $file;
    
    private $s_key = '!@ndy1#2%3';
    
    public function rules()
    {
        return [
            [
                ['file'],
                'required',
                'on' => [self::SCENARIO_IMPORT],
                'message' => '{attribute}不能为空',
            ],
            ['file','file', 'extensions' => ['xlsx','xls'],'checkExtensionByMimeType'=>false,'message'=>'文件格式错误'],
            ['file','checkFile'],
        ];
    }
    
    public function checkFile($attribute)
    {
        if (!$this->hasErrors()) {
            $file_name = $this->$attribute->name;
        }
    }
    
    public function scenarios()
    {
        return [
            self::SCENARIO_IMPORT => ['file'],
            
        ];
    }
    
    public function import($user)
    {
        $file = $this->file;
        
        $file_name = mb_substr($file->name,0,mb_strpos($file->name, '.'));
        if(0 >= strtotime($file_name)){
            return ['status'=>false,'msg'=>'文件名不是日期'];
        }
        
        $arr = Excel::import($file->tempName, [
            'setFirstRecordAsKeys' => false,
            'setIndexSheetByName' => true,
            'getOnlySheet' => 'Sheet1',
        ]);
        if($this->checkTitle($arr[1]) && $arr[2]){
            array_shift($arr);
            $date = date('Ym',strtotime($file_name));
            $sql = "INSERT INTO `oa_salary` (`empno`, `date`, `cost_depart`, `depart`, `position`, `name`, `base_salary`, `jixiao`, `need_workdays`, `static_workdays`, `static_salary`, `holiday_salary`, `away_subsidy`, `other_subsidy`, `forfeit`, `staitic_salary`, `jixiao_money`, `xiao_salary`, `shebao`, `gongjijin`, `before_tax_salary`, `tax`, `illness_money`, `after_tax_salary`, `after_tax_salary_person`, `des`, `id_card`, `bank_card`, `bank_name_des`, `yanglao`, `yiliao`, `shiye`, `entry_time`) VALUES ";
            foreach($arr as $v){ 
                $sql .= <<<jdf
                (
                '{$v['B']}',
                '{$date}',
                '{$v['C']}',
                '{$v['D']}',
                '{$v['E']}',
                '{$v['F']}',
                AES_ENCRYPT('{$v['G']}','{$this->s_key}'),
                AES_ENCRYPT('{$v['H']}','{$this->s_key}'),
                '{$v['I']}',
                '{$v['J']}',
                AES_ENCRYPT('{$v['K']}','{$this->s_key}'),
                AES_ENCRYPT('{$v['L']}','{$this->s_key}'),
                AES_ENCRYPT('{$v['M']}','{$this->s_key}'),
                AES_ENCRYPT('{$v['N']}','{$this->s_key}'),
                AES_ENCRYPT('{$v['O']}','{$this->s_key}'),
                AES_ENCRYPT('{$v['P']}','{$this->s_key}'),
                AES_ENCRYPT('{$v['Q']}','{$this->s_key}'),
                AES_ENCRYPT('{$v['R']}','{$this->s_key}'),
                AES_ENCRYPT('{$v['S']}','{$this->s_key}'),
                AES_ENCRYPT('{$v['T']}','{$this->s_key}'),
                AES_ENCRYPT('{$v['U']}','{$this->s_key}'),
                AES_ENCRYPT('{$v['V']}','{$this->s_key}'),
                AES_ENCRYPT('{$v['W']}','{$this->s_key}'),
                AES_ENCRYPT('{$v['X']}','{$this->s_key}'),
                AES_ENCRYPT('{$v['Y']}','{$this->s_key}'),
                '{$v['Z']}',
                AES_ENCRYPT('{$v['AA']}','{$this->s_key}'),
                AES_ENCRYPT('{$v['AB']}','{$this->s_key}'),
                AES_ENCRYPT('{$v['AC']}','{$this->s_key}'),
                AES_ENCRYPT('{$v['AD']}','{$this->s_key}'),
                AES_ENCRYPT('{$v['AE']}','{$this->s_key}'),
                AES_ENCRYPT('{$v['AF']}','{$this->s_key}'),
                AES_ENCRYPT('{$v['AG']}','{$this->s_key}')
                ),
jdf;
            }
            $sql = substr($sql, 0, - 1);
            $res = yii::$app->db->createCommand($sql)->execute();
            $sql = base64_encode(yii::$app->getSecurity()->encryptByKey($sql,$this->s_key));
            SalaryLogic::instance()->addLog($sql,$user['person_id'],$user['person_name']);
            return ['status'=>true];
        }
        return ['status'=>false,'msg'=>'文件不正确，请重新下载模版后填入数据'];
    }
    
    public function decrypt($data)
    {
        $res = yii::$app->getSecurity()->decryptByKey(base64_decode($data),$this->s_key);
        return $res;
    }
    
    protected function checkTitle($title)
    {
        if($title['A'] == '序号' && 
            $title['B'] == '员工编号' &&
            $title['C'] == '成本中心' && 
            $title['D'] == '部门' &&
            $title['E'] == '职位' &&
            $title['F'] == '姓名' &&
            $title['G'] == '基本工资' &&
            $title['H'] == '绩效工资' &&
            $title['I'] == '应出勤天数' &&
            $title['J'] == '实出勤天数' &&
            $title['K'] == '实发工资' &&
            $title['L'] == '国定假日加班工资' &&
            $title['M'] == '出差补贴' &&
            $title['N'] == '其他补发' &&
            $title['O'] == '其他扣款' &&
            $title['P'] == '应发工资合计' &&
            $title['Q'] == '绩效奖金' &&
            $title['R'] == '孝工资' &&
            $title['S'] == '社保扣款' &&
            $title['T'] == '公积金扣款' &&
            $title['U'] == '税前工资' &&
            $title['V'] == '个人所得税' &&
            $title['W'] == '大病保险' &&
            $title['X'] == '税后工资' &&
            $title['Y'] == '税后工资(员工个人)' &&
            $title['Z'] == '备注' &&
            $title['AA'] == '身份证号码' &&
            $title['AB'] == '工资卡号' &&
            $title['AC'] == '开户行信息' &&
            $title['AD'] == '养老' &&
            $title['AE'] == '医疗' &&
            $title['AF'] == '失业' &&
            $title['AG'] == '入职日期' &&
            1 == 1
        ){
            return true;
        }
        return false;
    }
    
    
    public function getList($params,$user,$arrPersonRole)
    {
        $keywords = trim(ArrayHelper::getValue($params,'keywords',null));
        $page = ArrayHelper::getValue($params,'page',1);
        $page_size = ArrayHelper::getValue($params,'page_size',10);
        $date = ArrayHelper::getValue($params,'date',null);
         
        $query = Salary::find()->select([
            'empno' ,
            'date',
            'cost_depart' ,// '成本中心',
            'depart' ,//varchar(20) NOT NULL DEFAULT '' COMMENT '部门',
            'position',// varchar(20) NOT NULL DEFAULT '' COMMENT '职位',
            'name',// varchar(10) NOT NULL DEFAULT '' COMMENT '姓名',
            "AES_DECRYPT(base_salary,'{$this->s_key}') as base_salary",// tinyblob NOT NULL COMMENT '基本工资',
            "AES_DECRYPT(jixiao,'{$this->s_key}') as jixiao",// tinyblob NOT NULL COMMENT '绩效',
            'need_workdays',// tinyint(2) NOT NULL DEFAULT '0' COMMENT '应出勤天数',
            'static_workdays',// tinyint(2) NOT NULL DEFAULT '0' COMMENT '实际出勤天数',
            "AES_DECRYPT(static_salary,'{$this->s_key}') as static_salary",// tinyblob NOT NULL COMMENT '基本工资',
            "AES_DECRYPT(holiday_salary,'{$this->s_key}') as holiday_salary",// tinyblob NOT NULL COMMENT '国定假日加班工资',
            "AES_DECRYPT(away_subsidy,'{$this->s_key}') as away_subsidy",// tinyblob NOT NULL COMMENT '出差补贴',
            "AES_DECRYPT(other_subsidy,'{$this->s_key}') as other_subsidy",// tinyblob NOT NULL COMMENT '其他补贴',
            "AES_DECRYPT(forfeit,'{$this->s_key}') as forfeit",// tinyblob NOT NULL COMMENT '其他扣款',
            "AES_DECRYPT(staitic_salary,'{$this->s_key}') as staitic_salary",// tinyblob NOT NULL COMMENT '应发工资',
            "AES_DECRYPT(jixiao_money,'{$this->s_key}') as jixiao_money",// tinyblob NOT NULL COMMENT '绩效奖金',
            "AES_DECRYPT(xiao_salary,'{$this->s_key}') as xiao_salary",// tinyblob NOT NULL COMMENT '孝工资',
            "AES_DECRYPT(shebao,'{$this->s_key}') as shebao",// tinyblob NOT NULL COMMENT '社保',
            "AES_DECRYPT(gongjijin,'{$this->s_key}') as gongjijin",// tinyblob NOT NULL COMMENT '公积金',
            "AES_DECRYPT(before_tax_salary,'{$this->s_key}') as before_tax_salary",// tinyblob NOT NULL COMMENT '税前工资',
            "AES_DECRYPT(tax,'{$this->s_key}') as tax",// tinyblob NOT NULL COMMENT '个人所得税',
            "AES_DECRYPT(illness_money,'{$this->s_key}') as illness_money",// tinyblob NOT NULL COMMENT '大病保险',
            "AES_DECRYPT(after_tax_salary,'{$this->s_key}') as after_tax_salary",// tinyblob NOT NULL COMMENT '税后工资',
            "AES_DECRYPT(after_tax_salary_person,'{$this->s_key}') as after_tax_salary_person",// tinyblob NOT NULL COMMENT '税后工资 个人',
            'des',// varchar(100) NOT NULL DEFAULT '' COMMENT '备注',
            "AES_DECRYPT(id_card,'{$this->s_key}') as id_card",// tinyblob NOT NULL COMMENT '身份证',
            "AES_DECRYPT(bank_card,'{$this->s_key}') as bank_card",// tinyblob NOT NULL COMMENT '银行卡',
            "AES_DECRYPT(bank_name_des,'{$this->s_key}') as bank_name_des",// tinyblob NOT NULL COMMENT '开户行',
            "AES_DECRYPT(yanglao,'{$this->s_key}') as yanglao",// tinyblob NOT NULL COMMENT '养老',
            "AES_DECRYPT(yiliao,'{$this->s_key}') as yiliao",// tinyblob NOT NULL COMMENT 'yiliao',
            "AES_DECRYPT(shiye,'{$this->s_key}') as shiye",// tinyblob NOT NULL COMMENT '失业',
            "AES_DECRYPT(entry_time,'{$this->s_key}') as entry_time",// tinyblob NOT NULL COMMENT '入职时间',
        ]
        );
        //关键词
        if($keywords){
            $keywords = mb_convert_encoding($keywords,'UTF-8','auto');
            $query->andWhere(['like', 'name', $keywords]);
        }
        //日期
        if($date){
            $date = date('Ym',strtotime($date));
            $query->andWhere(['date'=>$date]);
        }
        //权限
        if(!SalaryLogic::instance()->isHr($arrPersonRole)){
            $emp = Employee::find()->where(['person_id' => $user['person_id']])->one();
            if($emp && $emp->empno){
                $query -> andWhere(['empno' => $emp->empno]);
            }else{
                return false;
            }
        }
        
        //分页
        $pagination = new Pagination([
            'defaultPageSize' => $page_size,
            'totalCount' => $query->count(),
        ]);
        //echo $query->createCommand()->getRawSql();die();
        $res = $query->orderBy("id desc")
        ->offset($pagination->offset)
        ->limit($pagination->limit)
        ->all();
         
        
        foreach($res as $k => $v){
            $res[$k]['id'] = $pagination->pageSize * $pagination->getPage() + $k + 1;
            $res[$k]['entry_time'] = date('Y-m-d',strtotime($v['entry_time']));
        }
         
        return [
            'res' => $res,
            'page' => BackLogic::instance()->pageFix($pagination)
        ];
    }
}