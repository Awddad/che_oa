import { query,revoke } from '../services/mysend';
import { parse } from 'qs';
import { message} from 'antd';

export default {
  namespace: 'mysend',
  state: {
    dataSource: [],
    field: '',
    type:3,
    keywords: '',
    start_time:'',
    end_time:'',
    loading: false,
    total: null,
    at:'',
    ob:'',
    sortingType:'',
    repayment:[],
    current: 1,
    perPage:'',
    currentPage:'',
    pageCount:'',
    currentItem: {},
    modalVisible: false,
    modalType: 'update',
  },

  subscriptions: {
    setup({ dispatch, history }) {
      history.listen(location => {
        if (location.pathname === '/mysend') {
          dispatch({
            type: 'query',
            payload: {
                type: 3,
                at: location.query.at == null? "" : location.query.at,
                ob:location.query.ob == null? "" : location.query.ob,
                pageCount:location.query.pageCount == null? "" : location.query.pageCount,
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
    },
    *search({ payload },{ call,put }){
        //console.log(payload);
        const { data } = yield call(query,payload);
        if(data && data.code === 200){
            yield put({
                type: 'querySuccess',
                payload:{
                    keywords: payload.keywords,
                    start_time: payload.start_time,
                    end_time: payload.end_time,
                    dataSource:data.data.res
                }
            });
        }
    },
    *filtersort({ payload },{ call,put }){
        const { data } = yield call(query,payload);
        if(data && data.code === 200){
            yield put({
                type: 'querySuccess',
                payload:{
                    keywords: payload.keywords,
                    start_time: payload.start_time,
                    end_time: payload.end_time,
                    dataSource:data.data.res,
                    at:payload.at
                }
            });
        }
    },
    *revoke({payload},{call,put}){
        const { data } = yield call(revoke,payload);
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
