<?php
namespace app\modules\oa_v1\logic;

use app\models\Job;

class Profession extends BaseLogic
{
	public function getList()
	{
		$jobs = Job::findAll(['is_delete'=>0]);
		$data = [];
		foreach ($jobs as $v) {
            $data[] = [
                'label' => $v->name,
                'value' => $v->id,
            ];
        }
        return $data;
	}
}