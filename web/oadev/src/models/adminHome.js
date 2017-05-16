import { queryTotal,queryChoiceCom,analogLogin} from '../services/adminHome';
import { parse } from 'qs';
import { message} from 'antd';
import { routerRedux } from 'dva/router';

export default {

  namespace: 'adminHome',

  state: {
    data: {},
    loanApply:{},
    loading: false,
    modalVisible: false,
    isLoginBefore:false,
  },

  subscriptions: {
    setup({ dispatch, history }) {
      history.listen(location => {
        if (location.pathname === '/adminHome') {
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
      /*const { data } = yield call(queryTotal, payload);
      if (data) {
        yield put({
          type: 'querySuccess',
          payload: {
        	  data: data.content,
          },
        });
      }*/
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
