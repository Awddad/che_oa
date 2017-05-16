import { query } from '../services/payment';
import { parse } from 'qs';
import { message} from 'antd';

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
                // keyword: location.query.keyword == null? "" : location.query.keyword,
                // begin_time: location.query.begin_time == null? "" : location.query.begin_time,
                // end_time: location.query.end_time == null? "" : location.query.end_time,
                // type: location.query.type == null? "" : location.query.type,
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
      if (data && data.code == 200) {
        yield put({
          type: 'querySuccess',
          payload: {
              dataSource: data.data.data,
              list: data.data.data,
              total: data.data.pages.totalCount,
              current: data.data.pages.currentPage,
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
                    keyword: payload.keyword,
                    begin_time: payload.begin_time,
                    end_time: payload.end_time,
                    dataSource:data.data.data,
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
