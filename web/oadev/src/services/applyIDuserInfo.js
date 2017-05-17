import request from '../utils/request';
import qs from 'qs';

export async function GetApplyID(params) {//申请单号
    return request('/oa_v1/default/get-apply-id', {
      method: 'post',
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
      },
      body: qs.stringify(params),
    });
}