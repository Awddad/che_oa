import { queryTotal,queryChoiceCom,analogLogin} from '../services/adminHome';
import { UserInfo } from '../services/adminHome';
import { parse } from 'qs';
import { message} from 'antd';
import { routerRedux } from 'dva/router';
import WebStorage from 'react-webstorage';
const webStorage = new WebStorage( window.sessionStorage || window.localStorage);

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
    },
    subscriptions: {
        setup({ dispatch, history }) {
          history.listen(location => {
            if (location.pathname === '/') {
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
            console.log(data);
            if (data && data.code == 200) {
                yield put({
                    type: 'querySuccess',
                    payload: {
                        userInfo:data.data,
                        personID:data.data.userinfo.person_id
                    },
                })
            }else if(data.code == 401 || data.code == 402){
                alert(0);
                window.location.href ="http://test.sso.checheng.net/login.php";
                //yield put(routerRedux.push({pathname:''}));
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
