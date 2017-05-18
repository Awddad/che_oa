import { queryTotal,queryChoiceCom,analogLogin,UserInfo} from '../services/adminHome';
import { parse } from 'qs';
import { message} from 'antd';
import { routerRedux } from 'dva/router';
import WebStorage from 'react-webstorage';
const webStorage = new WebStorage( window.sessionStorage || window.localStorage);
import { setCookie } from '../components/common';

export default {
    namespace: 'adminHome',
    state: {
        data: {},
        loanApply:{},
        personID:null,
        userInfo:null,
        loading: false,
        modalVisible: false,
        isLoginBefore:false,
        homeshowpage:false
    },
    subscriptions: {
        setup({ dispatch, history }) {
          history.listen(location => {
            //console.log(location);
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
            yield put({ type: 'showLoading' });
            const { data } = yield call(UserInfo);
            if (data && data.code == 200) {
                yield put({
                    type: 'querySuccess',
                    payload: {
                        userInfo:data.data,
                        personID:data.data.userinfo.person_id,
                        homeshowpage:true
                    },
                })
                setCookie("username",data.data.userinfo.person_name);
                setCookie("userID",data.data.userinfo.person_id);
            }else if(data.code == 401 || data.code == 402){
                window.location.href ="http://test.sso.checheng.net/login.php";
            }
        }
    },
    reducers: {
        showLoading(state) {
          return { ...state, loading: true };
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
