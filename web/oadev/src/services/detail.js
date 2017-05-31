import request from '../utils/request';
import qs from 'qs';

export async function PayMentConfirmquery(params) {//付款弹窗数据初始化
  return request(`/oa_v1/pay-confirm/form?${qs.stringify(params)}`);
}

export async function Detail(params) {//报销详情
  return request(`/oa_v1/apply/get-info?${qs.stringify(params)}`);
}

export async function PayMentConfirm(params) {//付款确认
    return request('/oa_v1/pay-confirm/index', {
      method: 'post',
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
      },
      body: qs.stringify(params),
    });
}

export async function RepayMentConfirmquery(params) {//还款弹窗数据初始化
  return request(`/oa_v1/back-confirm/form?${qs.stringify(params)}`);
}

export async function RepayMentConfirm(params) {//还款确认
    return request('/oa_v1/back-confirm/index', {
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

export async function GetUserInfo(params) {//申请单号
    return request('/oa_v1/default/get-user-info', {
      method: 'post',
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
      },
      body: qs.stringify(params),
    });
}


