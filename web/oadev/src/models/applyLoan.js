import { constCard,constPersonal,constCreate,addCard } from '../services/applyLoan';
import { parse } from 'qs';
import { message} from 'antd';
import { routerRedux } from 'dva/router';

export default {
  namespace: 'applyLoan',

  state: {
    constCard:[],
    constPersonal:[],
    carddata:[],
    constdata:[],
    copydata:[],
    resultbxtype:[],
    CardDetail:{},
    bank_name:'',
    bank_id:'',
    loading: false,
    isshowcardmodal:false,
    isshowconstmodal:false,
    isshowcopymodal:false,
    issubmitmodal:false
  },

  subscriptions: {
    setup({ dispatch, history }) {
      history.listen(location => {
        if (location.pathname === '/applyloan') {
          dispatch({
            type: 'query',
            payload: location.query,
          });
        }
      });
    },
  },

  effects: {
    *query({ payload }, { call, put }) {
      const { data } = yield call(constCard, payload);
      if (data) {
        yield put({
          type: 'querySuccess',
          payload:{
            constCard: data.data,
          },
        });
      }
    },
    *hideModal({payload},{call,put}){//隐藏弹窗
      yield put({
            type: 'hideModal1'
        });
    },
    *modelHandle({ payload }, { call, put }) {//数据新增刷新
      switch(payload.modalIndex){
         case 0:
            yield put({
                type: 'modelHandle1',
                payload:{...payload}
            });
          break;
        case 1:
          const response1  = yield call(constType, payload);
          if (response1.data.data) {
            yield put({
                type: 'modelHandle1',
                payload:{...payload,constType: response1.data.data}
            });
          }
          break;
        case 2:
          const  response2  = yield call(constPersonal, payload);
            yield put({
                type: 'modelHandle1',
                payload:{...payload,constPersonal:response2.data.data}
            });
          break;
      }
    },
    *addcard({payload},{call,put}){//新增银行卡
      const {data} = yield call(addCard, payload);
      if(data && data.code === 200){
        message.success("银行卡添加成功",2);
        const {data} = yield call(constCard , payload);
        if(data && data.code === 200){
            yield put({
              type: 'updateCard',
              constCard: data.data
            });
        }
      }else{
        message.error(data.message);
      }
    },
    *addconst({payload},{call,put,select}){//添加审批人
      const data = yield select(({ reimBurse }) => reimBurse.constdata );
      if(payload.type == 1){
        data.push(payload.row);
      }
      yield put({
          type: 'updateConst',
          payload: {
              constdata : data
          }
      });
    },
    *addcopy({payload},{call,put,select}){//添加抄送人
      const  data  = yield select(({ reimBurse }) => reimBurse.copydata );
      if(payload.type == 1){
        data.push(payload.row);
      }
      yield put({
          type: 'updateCopy',
          payload: {
              copydata : data
          }
      });
    },
    *create({payload},{call,put}){//提交报销单
      const { data } = yield call(constCreate, payload);
      if (data && data.code === 200) {
        //message.success('借款申请提交成功!');
        yield put(routerRedux.push({
          pathname: '/success',
          query: {
            apply_id:data.data,
            urltype:payload.urltype
          }
        }));
      } else {
        message.error(data.content, 5);
      }
    }
  },

  reducers: {
    showLoading(state) {
      return { ...state, loading: true };
    },
    querySuccess(state, action) {
      return { ...state, ...action.payload};
    },
    updateQueryKey(state, action) {
      return { ...state, ...action.payload};
    },
    updateCard(state,action){
      return { ...state, ...action.payload,isshowcardmodal: false};
    },
    updateConst(state,action){
      return { ...state, ...action.payload,isshowconstmodal: false};
    },
    updateCopy(state,action){
      return { ...state, ...action.payload,isshowcopymodal: false};
    },
    modelHandle1(state,action){
      return { ...state, ...action.payload};
    },
    table_del_modelHandle1(state,action){
      return { ...state, ...action.payload};
    },
    hideModal1(state, action) {
      return { ...state, isshowcardmodal:false, isshowconstmodal:false, isshowcopymodal:false ,issubmitmodal:false};
    },
  }
};
