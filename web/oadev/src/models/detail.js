import { query,Detail,RepayMentConfirmquery,RepayMentConfirm,PayMentConfirmquery,PayMentConfirm,Approval,GetUserInfo } from '../services/detail';
import { parse } from 'qs';
import { message} from 'antd';
import { routerRedux } from 'dva/router';

export default {
    namespace: 'Detail',
    state:{
        Baoxiao_Detail: {},
        Loan_Detail:{},
        RepayMent_Detail:{},
        isTitleStatus : '',
        ApplyID:null,
        personID:null,
        //还款
        isShowRepaymentConfirm:false,
        repaymentDepartmentData:{},
        repaymentaccountData:[],
        repaymentType:[],
        repaymentFlowaccount:[],
        repaymentTime:null,
        repaymentFlow:null,
        //付款
        isShowPaymentConfirm:false,
        paymentDepartmentData:{},
        paymentaccountData:[],
        paymentType:[],
        paymentFlowaccount:[],
        paymentTime:null,
        paymentFlow:null,
    },
    subscriptions: {
        setup({ dispatch, history }) {
          history.listen(location => {
            if (location.pathname === '/reimbursedetail') {
                dispatch({
                    type: 'BaoxiaoDetails',
                    payload: location.query,
                });
            }else if(location.pathname === '/loanmentdetail'){
                dispatch({
                    type: 'LoanDetails',
                    payload: location.query,
                });
            }else if(location.pathname === '/repaymentdetail'){
                dispatch({
                    type: 'RepayMentDetails',
                    payload: location.query,
                });
            }
          });
        },
    },
    effects: {
        *BaoxiaoDetails({ payload }, { call, put }) {//报销详情
            const { data } = yield call(Detail,{...payload,type:1});
            const  response2 = yield call(GetUserInfo,{});
            if (data && data.code === 200 && response2.data && response2.data.code == 200) {
                yield put({
                  type: 'refreshstaste',
                  payload:{
                    Baoxiao_Detail: data.data,
                    isTitleStatus:payload.type,
                    ApplyID:data.data.apply_id,
                    personID:response2.data.data.userinfo.person_id,
                  },
                });
            }
        },
        *LoanDetails({ payload }, { call, put }) {//借款详情
            const { data } = yield call(Detail,{...payload,type:2});
            const  response2 = yield call(GetUserInfo,{});
            if (data && data.code === 200 && response2.data && response2.data.code == 200 ) {
                yield put({
                  type: 'refreshstaste',
                  payload:{
                    Loan_Detail: data.data,
                    isTitleStatus:payload.type,
                    ApplyID:data.data.apply_id,
                    personID:response2.data.data.userinfo.person_id,
                  },
                });
            }
        },

        *PayMentConfirmQuery({ payload }, { call, put }) {//付款确认弹窗初始化
            const { data } = yield call(PayMentConfirmquery, payload);
            let response=null,dataname=null,detail=null;
            if(payload.type == 'bx'){
                response = yield call(Detail,{...payload,type:1});
                detail = response.data.data;
                dataname = {'Baoxiao_Detail':detail};
            }else{
                response = yield call(Detail,{...payload,type:2});
                detail = response.data.data;
                dataname = {'Loan_Detail':detail};
            }

            if (data && data.code === 200 && response && response.data.code == 200) {
                yield put({
                    type: 'refreshstaste',
                    payload:{
                        ...dataname,
                        isShowPaymentConfirm:payload.isShowPaymentConfirm,
                        paymentDepartmentData:data.data.pay_org,
                        paymentaccountData:data.data.pay_bank,
                        paymentType:data.data.tags,
                        ApplyID:payload.apply_id
                    }
                });
            }else{
                message.error(data.message,3);
            }
        },
        *PayMentConfirm({ payload }, { call, put }) {//付款确认
            const { data } = yield call(PayMentConfirm , payload);
            if (data && data.code === 200) {
                yield put({
                    type: 'refreshstaste',
                    payload:{
                        isShowPaymentConfirm:false
                    }
                });
                message.success("确认成功!",3);
                yield put(routerRedux.push({ pathname: '#/payment' }));
            }else{
                message.error(data.message,3);
            }
        },



        *RepayMentDetails({ payload }, { call, put }) {//还款详情
            const { data } = yield call(Detail,{...payload,type:3});
            const  response2 = yield call(GetUserInfo,{});
            if (data && data.code === 200 && response2.data && response2.data.code == 200) {
                yield put({
                  type: 'refreshstaste',
                  payload:{
                    RepayMent_Detail: data.data,
                    isTitleStatus:payload.type,
                    ApplyID:data.data.apply_id,
                    personID:response2.data.data.userinfo.person_id,
                  },
                });
            }
        },
        *RepayMentConfirmQuery({ payload }, { call, put }) {//还款确认弹窗初始化
            const { data } = yield call(RepayMentConfirmquery, payload);
            if (data && data.code === 200) {
                yield put({
                    type: 'refreshstaste',
                    payload:{
                        isShowRepaymentConfirm:payload.isShowRepaymentConfirm,
                        repaymentDepartmentData:data.data.pay_org,
                        repaymentaccountData:data.data.pay_bank,
                        repaymentType:data.data.tags,
                        ApplyID:payload.apply_id
                    }
                });
            }else{
                message.error(data.message,3);
            }
        },
        *RepayMentConfirm({ payload }, { call, put }) {//还款确认
            const { data } = yield call(RepayMentConfirm , payload);
            if (data && data.code === 200) {
                yield put({
                    type: 'refreshstaste',
                    payload:{
                        isShowRepaymentConfirm:false,
                        RepayMent_Detail: data.data,
                        isTitleStatus:payload.type,
                    }
                });
                message.success("确认成功!",3);
                yield put(routerRedux.push({ pathname: '#/make_collections' }));
            }else{
                message.error(data.message,3);
            }
        },

        *Approval({ payload }, { call, put }) { //审批
            const { data } = yield call(Approval, payload);
            if (data && data.code === 200) {
                message.success("确认成功!",3);
                yield put(routerRedux.push({ pathname: payload.url }));
            }else{
                message.error(data.message,3);
            }
        },


        *hideModal({ payload }, { call, put }) {//关闭弹窗
            yield put({
                type: 'refreshstaste',
                payload:payload
            });
        }
    },
    reducers: {
        refreshstaste(state,action) {
            return { ...state, ...action.payload};
        }
    }
}