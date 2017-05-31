import { query,revoke,GetUserInfo } from '../services/mysend';
import { parse } from 'qs';
import { message} from 'antd';
import { userLogin,MenuKey } from '../components/common';

export default {
  namespace: 'mysend',
  state: {
    loading: false,
    dataSource: [],
    field: '',
    type:3,
    keywords: '',
    start_time:'',
    end_time:'',
    total: null,
    at:'',
    sort:'',
    status:'',
    current: 1,
    perPage:'',
    currentPage:'',
    pageCount:'',
    totalCount:'',
    currentItem: {},
    modalVisible: false,
    modalType: 'update',
    personID:null,
  },

  subscriptions: {
    setup({ dispatch, history }) {
      history.listen(location => {
        if (location.pathname === '/mysend') {
          MenuKey('/mysend');
          dispatch({
            type: 'query',
            payload: {
                type: 3,
                page_size:10,
            },
          });
        }
      });
    },
  },

  effects: {
    *query({ payload }, { call, put }) {
      yield put({ type: 'showLoading' });
      yield put({
        type: 'updateQueryKey',
        payload: { page: 1,},
      });
      const { data } = yield call(query, payload);
      const  response2 = yield call(GetUserInfo,{});
      yield put({ type: 'hideLoading' });
      if (data && data.code == 200 && response2.data && response2.data.code == 200) {
        yield put({
          type: 'querySuccess',
          payload: {
              dataSource: data.data.res,
              total: data.data.page.totalCount,
              current: data.data.page.currentPage,
              perPage:data.data.page.perPage,
              personID:response2.data.data.userinfo.person_id,
          },
        });
      }
    },
    *search({ payload },{ call,put }){
        yield put({ type: 'showLoading' });
        const { data } = yield call(query,payload);
        yield put({ type: 'hideLoading' });
        if(data && data.code === 200){
            yield put({
                type: 'querySuccess',
                payload:{
                    dataSource: data.data.res,
                    total: data.data.page.totalCount,
                    current: data.data.page.currentPage,
                    perPage:data.data.page.perPage,
                    keywords: payload.keywords,
                    start_time: payload.start_time,
                    end_time: payload.end_time,
                    current:1
                }
            });
        }
    },
    *filtersort({ payload },{ call,put }){
        yield put({ type: 'showLoading' });
        const { data } = yield call(query,payload);
        yield put({ type: 'hideLoading' });
        if(data && data.code === 200){
            yield put({
                type: 'querySuccess',
                payload:{
                    keywords: payload.keywords,
                    start_time: payload.start_time,
                    end_time: payload.end_time,
                    dataSource:data.data.res,
                    perPage:payload.page_size,
                    total: data.data.page.totalCount,
                    sort:payload.sort,
                    at:payload.at,
                    status:payload.status
                }
            });
        }
    },
    *revoke({payload},{call,put}){
        yield put({ type: 'showLoading' });
        const { data } = yield call(revoke,payload);
        yield put({ type: 'hideLoading' });
        if(data && data.code === 200){
            message.success("撤销成功",3);
            const { data } = yield call(query, payload);
            if (data && data.code == 200) {
              yield put({
                type: 'querySuccess',
                payload: {
                    dataSource: data.data.res,
                    total: data.data.page.totalCount,
                    current: data.data.page.currentPage,
                },
              });
            }
        }else{
            message.error("撤销失败",3);
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
    querySuccess(state, action) {
      return { ...state, ...action.payload, loading: false };
    },
    showModal(state, action) {
      return { ...state, ...action.payload, modalVisible: true};
    },
    hideModal(state) {
      return { ...state, modalVisible: false };
    },
    updateQueryKey(state, action) {
      return { ...state, ...action.payload };
    },
  },

};
