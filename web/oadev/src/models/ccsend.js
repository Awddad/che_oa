import { query } from '../services/ccsend';
import { parse } from 'qs';
import { message} from 'antd';
import { userLogin } from '../components/common';

export default {
  namespace: 'ccsend',
  state: {
    loading: false,
    dataSource: [],
    field: '',
    type:4,
    keywords: '',
    start_time:'',
    end_time:'',
    total: null,
    at:'',
    ob:'',
    sortingType:'',
    repayment:[],
    current: 1,
    perPage:'',
    currentPage:'',
    pageCount:'',
    totalCount:'',
    currentItem: {},
    modalVisible: false,
    modalType: 'update',
  },

  subscriptions: {
    setup({ dispatch, history }) {
      history.listen(location => {
        if (location.pathname === '/ccsend') {
          dispatch({
            type: 'query',
            payload: {
                type: 4,
                at: location.query.at == null? "" : location.query.at,
                ob:location.query.ob == null? "" : location.query.ob,
                status:location.query.status == null? "" : location.query.status,
                pageCount:location.query.pageCount == null? "" : location.query.pageCount,
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
        const { data } = yield call(query,payload);
        if(data && data.code === 200){
            yield put({
                type: 'querySuccess',
                payload:{
                    keywords: payload.keywords,
                    start_time: payload.start_time,
                    end_time: payload.end_time,
                    dataSource:data.data.res,
                    total: data.data.page.totalCount,
                    current: data.data.page.currentPage,
                    perPage:data.data.page.perPage,
                    current: 1,
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
                    at:payload.at,
                    ob:payload.ob,
                    status:payload.status,
                }
            });
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
