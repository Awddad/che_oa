import { query } from '../services/already-approve';
import { parse } from 'qs';
import { message} from 'antd';
import { userLogin } from '../components/common';

export default {
  namespace: 'alreadyApprove',
  state: {
    dataSource: [],
    field: '',
    type:2,
    keywords: '',
    start_time:'',
    end_time:'',
    loading: false,
    total: null,
    at:'',
    sort:'',
    status:'',
    sortingType:'',
    current: 1,
    perPage:'',
    currentItem: {},
    modalVisible: false,
    modalType: 'update',
  },
  subscriptions: {
    setup({ dispatch, history }) {
      history.listen(location => {
        if (location.pathname === '/already-approve') {
          dispatch({
            type: 'query',
            payload: {
                type: 2,
                page_size:10,
            },
          });
        }
      });
    },
  },

  effects: {
    *query({ payload }, { call, put }) {
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
                    total: data.data.page.totalCount,
                    current: data.data.page.currentPage,
                    perPage:data.data.page.perPage,
                    dataSource:data.data.res,
                    current:1
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
                    current: data.data.page.currentPage,
                    total:data.data.page.totalCount,
                    perPage:data.data.page.perPage,
                    dataSource:data.data.res,
                    sort:payload.sort,
                    at:payload.at
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
