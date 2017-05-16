import request from '../utils/request';
import qs from 'qs';


export async function queryTotal(params) {
  return request('/admin/AdminCtrl/queryTotal', {
    method: 'post',
    headers: {
      "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
    },
    body: qs.stringify(params),
  });
}
