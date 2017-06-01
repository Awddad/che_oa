import { UserInfo } from '../services/loading';
import { routerRedux } from 'dva/router';
import { message} from 'antd';
import WebStorage from 'react-webstorage';
const webStorage = new WebStorage( window.localStorage || window.sessionStorage);
import { setCookie } from '../components/common';
//延迟
//const delay = (timeout) => new Promise(resolve => setTimeout(resolve, timeout));

export default {
  namespace: 'Loading',
  state: {
    loginLoading: false,
  },
  subscriptions: {
    setup({ dispatch, history }) {
      history.listen(location => {
        if (location.pathname == "/") {
          dispatch({
            type: 'query',
          });
        }
      });
    },
  },
  effects: {
    *query({ payload }, { dispatch,put, call }) {
        const {data} = yield call(UserInfo, payload);
        if (data && data.code == 200) {
            localStorage.setItem('adminPms',Object.values(data.data.roleInfo));
            setCookie('adminPms',Object.values(data.data.roleInfo));
            yield put({
                type: 'querySuccess',
                payload:{
                    userInfo:data.data,
                    personID:data.data.userinfo.person_id,
                    homeshowpage:true
                }
            });
            localStorage.setItem("username",data.data.userinfo.person_name);
            setCookie("userID",data.data.userinfo.person_id);
            setCookie("department",data.data.userinfo.org_full_name);
            yield put(routerRedux.push('/adminhome'));
        }else if(data.code == 401 || data.code == 402){
            window.location.href ="http://test.sso.checheng.net/login.php";
        }
    },
    *userinfo({ payload }, { dispatch,put, call }) {
        const {data} = yield call(UserInfo, payload);
        if (data && data.code == 200) {
            localStorage.setItem('adminPms',Object.values(data.data.roleInfo));
            setCookie('adminPms',Object.values(data.data.roleInfo));
            yield put({
                type: 'querySuccess',
                payload:{
                    userInfo:data.data,
                    personID:data.data.userinfo.person_id,
                    homeshowpage:true
                }
            });
            localStorage.setItem("username",data.data.userinfo.person_name);
            setCookie("userID",data.data.userinfo.person_id);
            setCookie("department",data.data.userinfo.org_full_name);
        }else if(data.code == 401 || data.code == 402){
            window.location.href ="/oa_v1/login";
        }
    },
  },
  reducers: {
    querySuccess(state,action) {
      return {...state,...action.payload}
    }
  }
}
