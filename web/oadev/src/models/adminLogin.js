import { login, logout, updatePassword } from '../services/adminLogin';
import { parse } from 'qs';
import { message} from 'antd';
import { routerRedux } from 'dva/router';
import WebStorage from 'react-webstorage';
const webStorage = new WebStorage(window.sessionStorage || window.localStorage);

function delay(timeout){
  return new Promise(resolve => {
    setTimeout(resolve, timeout);
  });
}

export default {

  namespace: 'adminLogin',

  state: {
    loading:false,
    adminPms:[]
  },

  subscriptions: {
    setup({ dispatch, history }) {
      history.listen(location => {
    	  if (location.pathname === '/logout') {
          dispatch({
            type: 'logout',
          });
        }
    });
  }
},

effects: {
  *login({ payload }, {select, call, put,dispatch }) {
    yield put({ type: 'showLoading' });
    console.log(dispatch);
    const { data } = yield call(login, payload);
    if (data && data.statusCode==1) {
      webStorage.setItem("adminPms", data.content.pmsIds);
      webStorage.setItem("name", data.content.name);
      yield put({ type: 'loginSuccess',payload:{adminPms:data.content}});
      /*webStorage.setItem("menuKey", "/article");*/
      webStorage.setItem("OneCrumb", "首页");
      webStorage.setItem("TwoCrumb", "概览");
      yield put(routerRedux.push({
        pathname: 'adminHome'
      }));
    }else{
      yield put({ type: 'hideLoading' });
      message.error(data.content, 5);
    }
  },
  *updatePassword({ payload }, {select, call, put }) {
	    yield put({ type: 'showLoading' });
	    const { data } = yield call(updatePassword, payload);
	    if (data && data.statusCode==1) {
	      message.success("密码修改成功,请重新登录");
        //延迟跳转
        yield call(delay, 1500);
	      yield put(routerRedux.push({
	        pathname: '/logout'
	      }));
	    }else{
	        yield put({ type: 'hideLoading' });
	        message.error(data.content, 5);
	    }
	  },
  *logout({ payload }, {select, call, put }) {
    yield put({ type: 'showLoading' });
    const { data } = yield call(logout, payload);
    if (data && data.statusCode==1) {
      yield put(routerRedux.push({
        pathname: '/admin'
      }));
    }
  }
},

  reducers: {
    showLoading(state) {
        return { ...state, loading: true };
    },
    hideLoading(state) {
      return { ...state, loading: false };
    },
    loginSuccess(state,action){
      return { ...state,...action.payload, loading: false };
    }
  }
};
