import request from '../utils/request';
import qs from 'qs';

export async function PayMentConfirmquery(params) {//付款弹窗数据初始化
  return request(`/oa_v1/pay-confirm/form?${qs.stringify(params)}`);
}

export async function BaoxiaoDetail(params) {//报销详情
  return request(`/oa_v1/apply/get-baoxiao?${qs.stringify(params)}`);
}

export async function LoanDetail(params) {//借款详情
  return request(`/oa_v1/apply/get-jiekuan?${qs.stringify(params)}`);
}

export async function PayMentConfirm(params) {//付款确认
    return request('/oa_v1/pay-confirm', {
      method: 'post',
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
      },
      body: qs.stringify(params),
    });
}

export async function RepayMentDetail(params) {//还款详情
  return request(`/oa_v1/apply/get-payback?${qs.stringify(params)}`);
}

export async function RepayMentConfirmquery(params) {//还款弹窗数据初始化
  return request(`/oa_v1/back-confirm/form?${qs.stringify(params)}`);
}

export async function RepayMentConfirm(params) {//还款确认
    return request('/oa_v1/back-confirm', {
      method: 'post',
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
      },
      body: qs.stringify(params),
    });
}

export async function Approval(params) {//审批
  return request('/oa_v1/approval-log/update', {
      method: 'post',
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
      },
      body: qs.stringify(params),
  });
}

