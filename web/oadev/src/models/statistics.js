import { query, department } from '../services/statistics';
import { parse } from 'qs';
import { message} from 'antd';
import { userLogin } from '../components/common';

export default {
  namespace: 'Statistics',
  state: {
    dataSource: [],
    department:null,
    field: '',
    keywords: '',
    start_time:'',
    end_time:'',
    loading: false,
    total: null,
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
        if (location.pathname === '/statistics') {
            userLogin();
          dispatch({
            type: 'query',
            payload: {
                pageCount:location.query.pageCount == null? "" : location.query.pageCount,
                pageSize:10,
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
        const response = yield call(query, payload);
        const response1 = yield call(department,payload);
        if (response.data && response.data.code == 200) {
            yield put({
                type: 'querySuccess',
                payload: {
                    dataSource: response.data.data.info,
                    total: response.data.data.pages.totalCount,
                    current: response.data.data.pages.currentPage,
                    perPage: response.data.data.pages.perPage
                },
            });
        }
        if(response1.data && response1.data.code === 200){
            yield put({
                type: 'querySuccess',
                payload:{
                    department:response1.data.data,
                }
            });
        }
    },
    *search({ payload },{ call,put }){
        //console.log(payload)
        const { data } = yield call(query,payload);
        if(data && data.code === 200){
            yield put({
                type: 'querySuccess',
                payload:{
                    key: payload.key,
                    time: payload.time,
                    dataSource:data.data.info,
                    current:data.data.pages.currentPage,
                    total:data.data.pages.totalCount
                }
            });
        }
    },
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
