<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_salary".
 *
 * @property integer $id
 * @property string $empno
 * @property string $date
 * @property integer $org_id
 * @property string $cost_depart
 * @property string $depart
 * @property string $position
 * @property string $name
 * @property string $base_salary
 * @property string $jixiao
 * @property integer $need_workdays
 * @property integer $static_workdays
 * @property string $static_salary
 * @property string $holiday_salary
 * @property string $away_subsidy
 * @property string $other_subsidy
 * @property string $forfeit
 * @property string $staitic_salary
 * @property string $jixiao_money
 * @property string $xiao_salary
 * @property string $shebao
 * @property string $gongjijin
 * @property string $before_tax_salary
 * @property string $tax
 * @property string $illness_money
 * @property string $after_tax_salary
 * @property string $after_tax_salary_person
 * @property string $des
 * @property string $id_card
 * @property string $bank_card
 * @property string $bank_name_des
 * @property string $yanglao
 * @property string $yiliao
 * @property string $shiye
 * @property string $entry_time
 */
class Salary extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_salary';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['empno', 'date', 'org_id', 'base_salary', 'jixiao', 'static_salary', 'holiday_salary', 'away_subsidy', 'other_subsidy', 'forfeit', 'staitic_salary', 'jixiao_money', 'xiao_salary', 'shebao', 'gongjijin', 'before_tax_salary', 'tax', 'illness_money', 'after_tax_salary', 'after_tax_salary_person', 'id_card', 'bank_card', 'bank_name_des', 'yanglao', 'yiliao', 'shiye', 'entry_time'], 'required'],
            [['org_id', 'need_workdays', 'static_workdays'], 'integer'],
            [['base_salary', 'jixiao', 'static_salary', 'holiday_salary', 'away_subsidy', 'other_subsidy', 'forfeit', 'staitic_salary', 'jixiao_money', 'xiao_salary', 'shebao', 'gongjijin', 'before_tax_salary', 'tax', 'illness_money', 'after_tax_salary', 'after_tax_salary_person', 'id_card', 'bank_card', 'bank_name_des', 'yanglao', 'yiliao', 'shiye', 'entry_time'], 'string'],
            [['empno', 'cost_depart', 'depart', 'position'], 'string', 'max' => 20],
            [['date'], 'string', 'max' => 6],
            [['name'], 'string', 'max' => 10],
            [['des'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'empno' => 'Empno',
            'date' => 'Date',
            'org_id' => 'Org ID',
            'cost_depart' => 'Cost Depart',
            'depart' => 'Depart',
            'position' => 'Position',
            'name' => 'Name',
            'base_salary' => 'Base Salary',
            'jixiao' => 'Jixiao',
            'need_workdays' => 'Need Workdays',
            'static_workdays' => 'Static Workdays',
            'static_salary' => 'Static Salary',
            'holiday_salary' => 'Holiday Salary',
            'away_subsidy' => 'Away Subsidy',
            'other_subsidy' => 'Other Subsidy',
            'forfeit' => 'Forfeit',
            'staitic_salary' => 'Staitic Salary',
            'jixiao_money' => 'Jixiao Money',
            'xiao_salary' => 'Xiao Salary',
            'shebao' => 'Shebao',
            'gongjijin' => 'Gongjijin',
            'before_tax_salary' => 'Before Tax Salary',
            'tax' => 'Tax',
            'illness_money' => 'Illness Money',
            'after_tax_salary' => 'After Tax Salary',
            'after_tax_salary_person' => 'After Tax Salary Person',
            'des' => 'Des',
            'id_card' => 'Id Card',
            'bank_card' => 'Bank Card',
            'bank_name_des' => 'Bank Name Des',
            'yanglao' => 'Yanglao',
            'yiliao' => 'Yiliao',
            'shiye' => 'Shiye',
            'entry_time' => 'Entry Time',
        ];
    }
}
