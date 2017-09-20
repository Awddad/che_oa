<?php
namespace app\modules\oa_v1\models;

use app\modules\oa_v1\logic\BaseLogic;

use Yii;
use app\models as appmodel;
use yii\db\Exception;
use yii\web\UploadedFile;


class BaoxiaoForm extends BaseForm
{
	public $user;
	public $apply_id;//流水号
	public $type = 1;//申请类型
	public $bank_card_id;//收款银行卡号
	public $bank_name;//收款银行
	public $bank_name_des;//支行
	public $bao_xiao_list;//报效明细
	public $approval_persons;//审批人
	public $copy_person;//抄送人
	public $cai_wu_need = 2;//需要财务确认
	public $files;//附件
	public $pics;//图片
	public $money = 0;//报效金额
	public $create_time;//创建时间
	public $title;//标题
	
	public function rules(){
		return [
			[
				['bank_card_id','bank_name','bao_xiao_list','approval_persons','apply_id'],
				'required',
				'message'=>'{attribute}不能为空'
			],
			['apply_id','unique','targetClass' => '\app\models\Apply', 'message' => '申请单已存在.'],
			//['bank_card_id','match','pattern'=>'/^(\d{16}|\d{19})$/','message'=>'银行卡不正确'],
			[
				['approval_persons', 'copy_person'],
				'each',
				'rule' => ['integer']
			],
			[
				['approval_persons', 'copy_person'], 'checkTotal'
			],
				
			['bao_xiao_list','validateList'],
			[['files','pics'],'safe'],
            ['apply_id', 'checkOnly']
		];
	}
	public function validateList($attribute)
	{
		if (!$this -> hasErrors()) {
			foreach($this->$attribute as $v){
				if($v['money'] <= 0){
					$this->addError($attribute, "报销金额不正确");
				}
				elseif(!$v['des']){
					$this->addError($attribute, "报销事项不正确!");
				}
				if ($this->hasErrors()){
					return;
				}
			}
		}
	}
    
    /**
     * 存储报销单
     * @param  appmodel\Person $person
     *
     * @return bool|string
     */
	public function saveBaoxiao($person)
	{
		$model_apply = $this->setApply($person);
		$transaction = Yii::$app -> db -> beginTransaction();
		try{
			if($model_apply -> insert()){
				$this -> saveBaoxiaoList();
				$this -> baoxiao();
				$this -> approvalPerson($model_apply);
				$this -> copyPerson($model_apply);
				
				$transaction -> commit();
                $person = appmodel\Person::findOne($this->approval_persons[0]);
                if($person->bqq_open_id) {
                    $typeName = appmodel\Apply::TYPE_ARRAY[$this->type];
                    $data = [
                        'tips_title' => 'OA -' .$typeName. '申请',
                        'tips_content' => '员工'.$model_apply->person.'发起'. $typeName.'申请，请在OA系统进行审批处理',
                        'receivers' => $person->bqq_open_id,
                    ];
                    BaseLogic::instance()->sendQqMsg($data);
                }
                return $this -> apply_id;
			}
			$this->addError('',current($model_apply->getFirstErrors()));
			return false;
		}catch(Exception $e){
			$transaction -> rollBack();
			$this->addError('',$e->getMessage());
			return false;
		}
	}

	/**
	 * 初始化model
	 * @param string $type ['baoxiao','baoxiaolist']
	 * @param object $model AR对象
	 * @param array $data
	 */
	protected function loadModel($type,&$model,$data=[])
	{
		if('baoxiao' == $type){
			$model -> apply_id = $this -> apply_id;
			$model -> bao_xiao_list_ids = implode(',',array_column($this ->bao_xiao_list,'id'));
			$model -> money = $this -> money;
			$model -> bank_card_id = $this -> bank_card_id;
			$model -> bank_name = $this -> bank_name;
			$model -> bank_name_des = $this -> bank_name_des?:'';
			$model -> files = json_encode($this -> files);
			//$model -> pics = $this -> pics?implode(',',$this -> pics):'';
			$model -> pics = $this -> pics ? : '';
		}elseif('baoxiaolist' == $type){
            /*$tag = appmodel\TagTree::findOne($data['type']);
            if(empty($tag)) {
                $typeName = '';
            } else {
                $typeName = $tag->name;
            }*/
			$model -> apply_id = $this -> apply_id;
			$model -> money = $data['money'];
			//$model -> type_name = $typeName;
			//$model -> type = $data['type'];
			$model -> des = $data['des'];
		}
	}
	
	protected function saveBaoxiaoList()
	{
		$model_biaoxiao_list = new appmodel\BaoXiaoList();
		foreach($this -> bao_xiao_list as &$v){
			$_model_biaoxiao_list = clone $model_biaoxiao_list;
			$this -> money += $v['money'];
			$this -> loadModel('baoxiaolist',$_model_biaoxiao_list,$v);
			if($_model_biaoxiao_list -> insert()){
				$v['id'] = $_model_biaoxiao_list -> id;
			}else{
				throw new Exception(current($_model_biaoxiao_list->getErrors())[0]);
				//throw new \Exception('明细失败');
			}
		}
	}
	protected function baoxiao()
	{
		$model_bao_xiao = new appmodel\BaoXiao();
		$this -> loadModel('baoxiao',$model_bao_xiao);
		if(!$model_bao_xiao -> insert()){
			throw new Exception(current($model_bao_xiao->getErrors())[0]);
			//throw new \Exception('报销失败');
		}
	}
	/**
	 * 保存文件
	 * @param UploadedFile $file
	 * @param string $dir
	 * @return array
	 */
	public function saveFile($file,$dir)
	{
		$base_dir = str_replace('\\', '/', Yii::$app -> basePath);
		$root_path = $base_dir.'/web'.$dir;

		if($file && !is_dir($root_path)){
			mkdir($root_path,0777,true);
		}
		$res = [];
		/**
		 * @var $v UploadedFile
		 */
		foreach($file as $v){
			$base_name = iconv("UTF-8","gb2312", $v -> baseName);
			$tmp_file_name = $base_name.$this->user['id'].rand(100,999).'.'.$v -> extension;
			
			if($v -> saveAs($root_path.'/'.$tmp_file_name))
				$res[] = [
							'name' => $base_name,
							'ext' => $v -> extension,
							'url' => $dir.'/'.$tmp_file_name
						]; 
		}
		return $res;
	}
    

}