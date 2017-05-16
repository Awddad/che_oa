import request from '../utils/request';
import qs from 'qs';

/**
 * 查询接口
 */
export async function query(params) {
  return request(`/oa_v1/apply/get-list?${qs.stringify(params)}`);
}

export async function revoke(params) { //撤销申请
  return request(`/oa_v1/apply/revoke?${qs.stringify(params)}`);
}

