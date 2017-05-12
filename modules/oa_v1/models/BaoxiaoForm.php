<?php
namespace app\modules\oa_v1\models;

use Yii;
use app\models as appmodel;
use yii\web\UploadedFile;
use app\commands\MyTcPdf;
use app\modules\oa_v1\logic\PersonLogic;


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
	public $fujian;//附件
	public $pic;//图片
	public $money = 0;//报效金额
	public $create_time;//创建时间
	public $title;//标题
	
	public function rules(){
		return [
			[
				['bank_card_id','bank_name','bank_name_des','bao_xiao_list','approval_persons','copy_person'],
				'required',
				'message'=>'{attribute}不能为空'
			],
			['bank_card_id','match','pattern'=>'/^(\d{16}|\d{19})$/','message'=>'银行卡不正确'],
			['approval_persons','validatePersons','params'=>'审批人'],
			['copy_person','validatePersons','params'=>'抄送人'],
			['bao_xiao_list','validateList'],
		];
	}
	public function validatePersons($attribute, $params)
	{
		if (!$this->hasErrors()) {
			foreach($this->$attribute as $k=>$v){
				$tmp[$k] = @$v['steep'];
			}
			array_multisort($this->$attribute, $tmp);
			$validator = new \yii\validators\NumberValidator();
			$validator -> integerOnly = true;
			foreach($this->$attribute as &$v){
				if(!$validator-> validate($v['person_id'])){
					$this->addError($attribute, "{$params}id不正确");
				}elseif(!$v['person_name'] = PersonLogic::instance() -> getPersonName($v['person_id'])){
					$this->addError($attribute, "{$params}id不正确！");
				}
				if ($this->hasErrors()){
					return;
				}
			}
		}
	}
	public function validateList($attribute)
	{
		if (!$this -> hasErrors()) {
			foreach($this->$attribute as $v){
				if($v['money'] <= 0){
					$this->addError($attribute, "报销金额不正确");
				}elseif(!$v['type_name']){
					$this->addError($attribute, "报销类型不正确");
				}elseif(!$v['type'] > 0){
					$this->addError($attribute, "报销类型不正确!");
				}
				if ($this->hasErrors()){
					return;
				}
			}
		}
	}
	
	/**
	 * 存储报销单
	 */
	public function saveBaoxiao()
	{
		$this -> apply_id = $this -> createId('apply');
		$model_apply = new appmodel\Apply();
		$this -> loadModel('apply',$model_apply);
		$transaction = Yii::$app -> db -> beginTransaction();
		try{
			if($model_apply -> insert()){
				$this -> saveBaoxiaoList();
				$this -> baoxiao();
				$this -> approvalLog();
				$this -> copyLog();
				//$this -> approvalPerson($model_apply);
				//$this -> copyPerson($model_apply);
				
				$transaction -> commit();
				return $this -> apply_id;
			}	
			return false;
		}catch(Exception $e){
			$transaction -> rollBack();
			return false;
		}
		
	}
	
	protected function createId($type)
	{
		$id = '';
		switch($type){
			case 'apply':
				$id = $this -> createApplyId();
				break;
			case 'baoxiaolist':
				$id = 'bl'.$this -> create_time.'22'.rand(10,99).rand(100,999);
				break;
		}
		return $id;
	}
	/**
	 * 初始化model
	 * @param string $type ['apply','baoxiao','baoxiaolist']
	 * @param object $model AR对象
	 * @param array $data
	 */
	protected function loadModel($type,&$model,$data=[])
	{
		if('apply' == $type){
			$model -> load(['Apply'=>(array)$this]);
			$model -> apply_id = $this -> apply_id;
			$model -> create_time = $this -> create_time;
			$model -> title = $this -> title;
			$model -> person = $this->user['name'];
			$model -> person_id = $this->user['id'];
			$model -> approval_persons = implode(',', array_column($model -> approval_persons,'person_name'));
			$model -> copy_person = implode(',', array_column($model -> copy_person,'person_name'));
			$approval_person = array_column($this -> approval_persons,'person_name')[0];
			$model -> next_des = "待{$approval_person}审批";
		}elseif('baoxiao' == $type){
			$model -> apply_id = $this -> apply_id;
			$model -> bao_xiao_list_ids = implode(',',array_column($this ->bao_xiao_list,'id'));
			$model -> money = $this -> money;
			$model -> bank_card_id = $this -> bank_card_id;
			$model -> bank_name = $this -> bank_name;
			$model -> bank_name_des = $this -> bank_name_des;
			$model -> files = json_encode($this -> fujian);
			$model -> pics = $this -> pic?implode(',',$this -> pic):'';
		}elseif('baoxiaolist' == $type){
			$model -> apply_id = $this -> apply_id;
			$model -> money = $data['money'];
			$model -> type_name = $data['type_name'];
			$model -> type = $data['type'];
			$model -> des = $data['des'];
		}elseif('approval_log' == $type){
			$model -> apply_id = $this -> apply_id;
			$model -> approval_person = $data['person_name'];
			$model -> approval_person_id = $data['person_id'];
			$model -> steep = $data['steep'];
			$model -> result = 0;
			$model -> is_end = isset($data['is_end']) ? $data['is_end']: 0;
			$model -> is_to_me_now = $data['steep'] == 1 ? 1 : 0;
		}elseif('copy' == $type){
			$model -> apply_id = $this -> apply_id;
			$model -> copy_person_id = $data['person_id'];
			$model -> copy_person = $data['person_name'];
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
				//throw new \Exception(current($model_bao_xiao->getErrors())[0]);
				throw new \Exception('明细失败');
			}
		}
	}
	protected function baoxiao()
	{
		$model_bao_xiao = new appmodel\BaoXiao();
		$this -> loadModel('baoxiao',$model_bao_xiao);
		if(!$model_bao_xiao -> insert()){
			//throw new \Exception(current($model_bao_xiao->getErrors())[0]);
			throw new \Exception('报销失败');
		}
	}
	protected function approvalLog()
	{
		$model_approval = new appmodel\ApprovalLog();
		foreach($this -> approval_persons as $k => &$v){
			if($k == count($this -> approval_persons)){
				$v['is_end'] = 1;
			}
			$_model_approval = clone $model_approval;
			$this -> loadModel('approval_log', $_model_approval, $v);
			if(!$_model_approval -> insert()){
				//throw new \Exception(current($model_bao_xiao->getErrors())[0]);
				throw new \Exception('审核人失败');
			}
		}
	}
	protected function copyLog()
	{
		$model_copy = new appmodel\ApplyCopyPerson();
		foreach($this -> copy_person as $k => &$v){
			$_model_copy = clone $model_copy;
			$this -> loadModel('copy', $_model_copy, $v);
			if(!$_model_copy -> insert()){
				//throw new \Exception(current($model_bao_xiao->getErrors())[0]);
				throw new \Exception('抄送人失败');
			}
		}
	}
	/**
	 * 保存文件
	 * @param unknown_type $file
	 * @param unknown_type $dir
	 */
	public function saveFile($file,$dir)
	{
		$base_dir = str_replace('\\', '/', Yii::$app -> basePath);
		$root_path = $base_dir.'/web'.$dir;

		if($file && !is_dir($root_path)){
			mkdir($root_path,0777,true);
		}
		$res = [];
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
	
	public function saveAccount($dir)
	{
		$arrInfo = [
			'apply_date' => date('Y年m月d日',$this -> create_time),
			'apply_id' => $this -> apply_id,
			'org_full_name' => $this -> title,
			'person' => $this -> user['name'],
			'bank_name' => $this -> bank_name.$this -> bank_name_des,
			'bank_card_id' => $this -> bank_card_id,
			'approval_person' => implode(',', array_column($this -> approval_persons,'person_name')),//多个人、分隔
			'copy_person' => implode(',', array_column($this -> copy_person,'person_name')),//多个人、分隔
			'list' => []
		];
		foreach($this -> bao_xiao_list as $v){
			$arrInfo['list'][] = [
					'type_name' => $v['type_name'],
					'money' => $v['money'],
					'detail' => $v['des']
					];
		}
		$base_dir = str_replace('\\', '/', Yii::$app -> basePath);
		$root_path = $base_dir.'/web'.$dir;
		if(!is_dir($root_path)){
			mkdir($root_path,0777,true);
		}
		
		$fileName = $arrInfo['apply_id'].'.pdf';
		$myPdf = new MyTcPdf();
		$myPdf -> createBaoXiaoDanPdf($root_path.'/'.$fileName, $arrInfo);
		appmodel\BaoXiao::updateAll(['bao_xiao_dan_pdf' => "$dir/$fileName"],"apply_id='{$this->apply_id}'");
	}
}