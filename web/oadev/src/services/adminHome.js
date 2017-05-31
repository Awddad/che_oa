import request from '../utils/request';
import qs from 'qs';

export async function UserInfo(params) { //撤销申请
  return request(`/oa_v1/default/get-user-info?${qs.stringify(params)}`);
}

export async function Loginout(params) { //撤销申请
  return request(`/oa_v1/default/login-out?${qs.stringify(params)}`);
}
