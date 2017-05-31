import { queryTotal,queryChoiceCom,analogLogin,UserInfo,Loginout} from '../services/adminHome';
import { parse } from 'qs';
import { message} from 'antd';
import { routerRedux } from 'dva/router';
import { setCookie,delCookie,userLogin } from '../components/common';

import WebStorage from 'react-webstorage';
const webStorage = new WebStorage( window.localStorage || window.sessionStorage);

export default {
    namespace: 'adminHome',
    state: {
        data: {},
        loanApply:{},
        personID:null,
        userInfo:null,
        modalVisible: false,
        isLoginBefore:false,
        homeshowpage:false,
        loading: false,
    },
    subscriptions: {
        setup({ dispatch, history }) {
          history.listen(location => {
            if (location.pathname != null) {
                dispatch({
                    type: 'query',
                    payload: location.query,
                });
            }
          });
        },
    },
    effects: {
        *query({ payload }, { call, put }) {
        },
        *loginout({ payload }, { call, put }) {
            const { data } = yield call(Loginout);
            if (data && data.code == 200) {
                delCookie("username");
                delCookie("department");
                delCookie("adminPms");
                delCookie("userID");
                webStorage.setItem('adminPms','');
                window.location.href = data.data.login_url;
            }
        }

    },
    reducers: {
        showLoading(state,action) {
            return { ...state, loading: true };
        },
        hideLoading(state) {
            return { ...state, loading: false };
        },
        querySuccess(state, action) {
          return { ...state, ...action.payload, loading: false };
        },
        showModal(state, action) {
          return { ...state, ...action.payload, modalVisible: true };
        },
        hideModal(state) {
          return { ...state, modalVisible: false };
        },
        updateQueryKey(state, action) {
          return { ...state, ...action.payload };
        }
    }
};
