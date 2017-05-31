import request from '../utils/request';
import qs from 'qs';

/**
 * 查询接口
 */
export async function query(params) {
  return request(`/oa_v1/jiekuan/index?${qs.stringify(params)}`);
}


export async function department(params) {
  return request(`/oa_v1/default/org?${qs.stringify(params)}`);
}

