import request from '../utils/request';
import qs from 'qs';

export async function constCard(params) {
  return request('/oa_v1/apply/get-bankcard',{
    method: 'post',
    headers: {
      "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
    },
    body: qs.stringify(params),
  });
}

export async function constPersonal(params) {
  return request('/oa_v1/apply/get-user-list',{
    method: 'post',
    headers: {
      "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
    },
    body: qs.stringify(params),
  });
}


export async function addCard(params) {
  return request('/oa_v1/apply/add-bankcard',{
    method: 'post',
    headers: {
      "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
    },
    body: qs.stringify(params),
  });
}

export async function constCreate(params) {
  return request('/oa_v1/loan/index',{
    method: 'post',
    headers: {
      "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
    },
    body: qs.stringify(params),
  });
}

export async function GetApplyID(params) {//申请单号
    return request(`/oa_v1/default/get-apply-id?${qs.stringify(params)}`);
}
