import { query } from '../services/payment';
import { parse } from 'qs';
import { message} from 'antd';
import { userLogin } from '../components/common';

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
      let total=0,perPage=null;
      if(Object.keys(data.data).length > 0){
          total = data.data.pages.totalCount;
          perPage = data.data.pages.perPage;
      }else{
          total = 0;
          perPage=10;
      }
      if (data && data.code == 200) {
        yield put({
          type: 'querySuccess',
          payload: {
              keyword: payload.keyword,
              dataSource: data.data.data,
              list: data.data.data,
              total: data.data.pages.totalCount,
              current: data.data.pages.currentPage,
              perPage: data.data.pages.perPage
          },
        });
      }
    },
    *search({ payload },{ call,put }){
        const { data } = yield call(query,payload);
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
        const { data } = yield call(query,payload);
        if(data && data.code === 200){
            yield put({
                type: 'querySuccess',
                payload:{
                    keyword: payload.keyword,
                    type:payload.type,
                    dataSource:data.data.data,
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
