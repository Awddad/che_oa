import request from '../utils/request';
import qs from 'qs';

/**
 * 查询接口
 */


export async function query(params) {
  return request('http://192.168.1.128:8010/oa_v1/back-confirm/list',{
    method: 'post',
    headers: {
      "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
    },
    body: qs.stringify(params),
  });
}