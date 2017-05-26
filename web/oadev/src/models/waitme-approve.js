import { query } from '../services/waitme-approve';
import { parse } from 'qs';
import { message} from 'antd';
import { userLogin } from '../components/common';

export default {
  namespace: 'waitme',
  state: {
        dataSource: [],
        field: '',
        type:1,
        keywords: '',
        start_time:'',
        end_time:'',
        loading: false,
        total: null,
        at:'',
        sort:'',
        sortingType:'',
        current: 1,
        currentItem: {},
        modalVisible: false,
        modalType: 'update',
    },

  subscriptions: {
    setup({ dispatch, history }) {
      history.listen(location => {
        if (location.pathname === '/waitmeapprove') {
          dispatch({
            type: 'query',
            payload: {
                type: 1,
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
          const { data } = yield call(query, payload);
          yield put({ type: 'hideLoading' });
          if (data && data.code == 200) {
            yield put({
              type: 'querySuccess',
              payload: {
                  dataSource: data.data.res,
                  total: data.data.page.totalCount,
                  current: data.data.page.currentPage,
                  perPage:data.data.page.perPage
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
                        keywords: payload.keywords,
                        total: data.data.page.totalCount,
                        current: data.data.page.currentPage,
                        perPage:data.data.page.perPage,
                        start_time: payload.start_time,
                        end_time: payload.end_time,
                        dataSource:data.data.res,
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
                        total:data.data.page.totalCount,
                        at:payload.at,
                        sort:payload.sort,
                    }
                });
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
