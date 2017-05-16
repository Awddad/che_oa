import { query } from '../services/make_collections';
import { parse } from 'qs';
import { message} from 'antd';

export default {
  namespace: 'make_collections',
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
    type:'',
    dataSource:[],
    perPage:null,
    modalVisible: false,
    modalType: 'update',
  },
    subscriptions: {
        setup({ dispatch, history }) {
          history.listen(location => {
            if (location.pathname === '/make_collections') {
              dispatch({
                type: 'query',
                payload: {
                    keyword: location.query.keyword == null? "" : location.query.keyword,
                    sorting: location.query.sorting == null? "" : location.query.sorting,
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
            if(data && data.code === 200){
                yield put({
                    type: 'querySuccess',
                    payload:{
                        keyword: payload.keyword,
                        begin_time: payload.begin_time,
                        end_time: payload.end_time,
                        dataSource:data.data.data,
                        list: data.data.data,
                        total:data.data.pages.totalCount,
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
                        keyword: payload.keywords,
                        begin_time: payload.begin_time,
                        end_time: payload.end_time,
                        dataSource:data.data.data,
                        type:payload.type
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
