<?php
namespace app\modules\oa_v1\controllers;


use yii;
use app\modules\oa_v1\models\ApplyOpenForm;
use app\models\Apply;

class ApplyOpenController extends BaseController
{
    public function actionAddApply()
    {
        $post = yii::$app->request->post();
        $model = new ApplyOpenForm();
        $model->load(['ApplyOpenForm'=>$post]);
        if(!$model->validate()){
            return $this->_returnError(403,current($model->getFirstErrors()));
        }
        $res = $model->saveApply($this->arrPersonInfo);
        if($res['status']){
            return $this->_return($res['apply_id']);
        }else{
            return $this->_returnError(400,$res['msg']);
        }
    }
	
	/**
	 * 开店统计
	 */
	public function actionGetOpenCount() {
		$data = [
            'all' => (int) Apply::find()->where(['type' => 13])->andWhere(['<>','status',4])->count(),
            'ing' => (int) Apply::find()->where(['type' => 13])->andWhere(['not in','status',[2,4,99]])->count(),
            'pass' => (int) Apply::find()->where(['type' => 13,'status' => 99])->count(),
            'fail' => (int) Apply::find()->where(['type' => 13,'status' => 2])->count()
        ];
        return $this->_return($data);
    }
    
    /**
     * 开店列表
     */
    public function actionGetOpenList()
    {
        $get = yii::$app->request->get();
        $model = new ApplyOpenForm();
        $res = $model->getOpenList($get);
        $data = [
			'page' => $res ['pages'],
			'res' => []
		];
		foreach ($res ['data'] as $k=>$v) {
			$data ['res'] [] = [
				'id' => ($data['page']['currentPage']-1)*$data['page']['perPage'] + $k+1,
				'apply_id' => $v ['apply_id'], // 审批单编号
				'date' => date('Y年m月d日', $v['create_time']), // 创建时间
				'city' => $v['district_name'], // 城市
				'address' => $v['address'], // 地址
				'rental' => $v['rental'], //租金
				'person' => $v['person'], //选址人
			    'status' => $v['status'] == 99 ? '通过' : ($v['status'] == 2 ? '不通过' : '待确认'),     
			]; 
		}
		return $this->_return($data, 200);
    }
     
}