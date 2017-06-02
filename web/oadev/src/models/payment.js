import { query,Export } from '../services/payment';
import { parse } from 'qs';
import { message} from 'antd';
import { userLogin,MenuKey } from '../components/common';

export default {
  namespace: 'payment',
  state: {
    list: [],
    field: '',
    loading: false,
    total: null,
    current: 1,
    currentItem: {},
    title:'',
    apply_id:'',
    create_time:'',
    type_name:'',
    money:'',
    sort:'',
    keyword:'',
    begin_time:'',
    start_time:'',
    perPage:null,
    repayment:[],
    dataSource:[],
    modalVisible: false,
    modalType: 'update',
  },

  subscriptions: {
    setup({ dispatch, history }) {
      history.listen(location => {
        if (location.pathname === '/payment') {
          MenuKey('/payment');
          dispatch({
            type: 'query',
            payload: {
                keyword: location.query.keyword == null? "" : location.query.keyword,
                begin_time: location.query.begin_time == null? "" : location.query.begin_time,
                end_time: location.query.end_time == null? "" : location.query.end_time,
                perPage:10
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
      let total=0,perPage=null,current=null;
      if (data && data.code == 200) {
        if(Object.keys(data.data).length > 0){
            total = data.data.pages.totalCount;
            current = data.data.pages.currentPage;
            perPage = data.data.pages.perPage;
        }else{
            total = 0;
            current = 1;
            perPage=10;
        }

        yield put({
          type: 'querySuccess',
          payload: {
              type:payload.type,
              keyword: payload.keyword,
              dataSource: data.data.data,
              list: data.data.data,
              total: total,
              current: current,
              perPage:perPage
          },
        });
      }
    },
    *search({ payload },{ call,put }){
        yield put({ type: 'showLoading' });
        const { data } = yield call(query,payload);
        yield put({ type: 'hideLoading' });
        let total=null,perPage=null;
        if(data.data.length > 0){
          total = data.data.pages.totalCount;
          perPage = data.data.pages.perPage;
        }else{
          total = 0;
          perPage=10;
        }
        if(data && data.code === 200){
            yield put({
                type: 'querySuccess',
                payload:{
                    keyword: payload.keyword,
                    begin_time: payload.begin_time,
                    end_time: payload.end_time,
                    dataSource:data.data.data,
                    total:total,
                    perPage:perPage
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
                    keyword: payload.keyword,
                    type:payload.type,
                    dataSource:data.data.data,
                    current: data.data.pages.currentPage,
                    total:data.data.pages.totalCount,
                    perPage:data.data.pages.perPage,
                    sort:payload.sort
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
