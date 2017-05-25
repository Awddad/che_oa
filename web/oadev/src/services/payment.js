import request from '../utils/request';
import qs from 'qs';

/**
 * 查询接口
 */

export async function query(params) {
  return request('/oa_v1/pay-confirm/list',{
    method: 'post',
    headers: {
      "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
    },
    body: qs.stringify(params),
  });
}

