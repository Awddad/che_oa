import request from '../utils/request';
import qs from 'qs';

export async function BaoxiaoDetail(params) {//报销详情
  return request(`/oa_v1/apply/get-baoxiao?${qs.stringify(params)}`);
}

export async function LoanDetail(params) {//借款详情
  return request(`/oa_v1/apply/get-jiekuan?${qs.stringify(params)}`);
}

export async function RepayMentDetail(params) {//还款详情
  return request(`/oa_v1/apply/get-payback?${qs.stringify(params)}`);
}
