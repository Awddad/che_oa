import { queryTotal,queryChoiceCom,analogLogin,UserInfo,Loginout} from '../services/adminHome';
import { parse } from 'qs';
import { message} from 'antd';
import { routerRedux } from 'dva/router';
import { setCookie } from '../components/common';

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
            /*yield put({ type: 'showLoading' ,payload:{aa:"111111"}});
            const { data } = yield call(UserInfo);
            yield put({ type: 'hideLoading' });
            if (data && data.code == 200) {
                webStorage.setItem('adminPms',['shen_qing_bao_xiao','shen_qing_bao_xiao','shen_qing_huang_kuan','dai_wo_shen_pi','wo_yi_shen_pi','wo_fa_qi_de','chao_song_gei_wo']);
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
                setCookie("department",data.data.userinfo.org_full_name);
                //console.log(webStorage.getItem('adminPms'));
            }else if(data.code == 401 || data.code == 402){
                window.location.href ="http://test.sso.checheng.net/login.php";
            }*/
        },
        *loginout({ payload }, { call, put }) {
            const { data } = yield call(Loginout);
            if (data && data.code == 200) {
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
