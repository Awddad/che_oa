import request from '../utils/request';
import qs from 'qs';

/**
 * 查询接口
 */
export async function query(params) {
  return request(`http://192.168.1.128:8010/oa_v1/jiekuan/index?${qs.stringify(params)}`);
}

