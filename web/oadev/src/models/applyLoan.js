import { constCard,constPersonal,constCreate,addCard,GetApplyID } from '../services/applyLoan';
import { parse } from 'qs';
import { message} from 'antd';
import { routerRedux } from 'dva/router';
import { userLogin,MenuKey } from '../components/common';
import WebStorage from 'react-webstorage';
const webStorage = new WebStorage(window.localStorage || window.sessionStorage);

export default {
  namespace: 'applyLoan',

  state: {
    constCard:[],
    constPersonal:null,
    copyPersonal:null,
    carddata:[],
    constdata:[],
    copydata:[],
    resultbxtype:[],
    CardDetail:{},
    bank_name:'',
    bank_id:'',
    addApplyID:null,//借款单ID
    department:null,
    bxname:null,
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
          MenuKey('/applyloan');
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
            let data = null;
            if( typeof(payload.constPersonal) != Array && payload.constPersonal == null ){
                const response = yield call(constPersonal, payload);
                data = response.data.data;
            }else{
                data = payload.constPersonal;
            }
            if (data) {
              yield put({
                  type: 'modelHandle1',
                  payload:{
                    ...payload,
                    constPersonal: data,
                  }
              });
            }
        break;
        case 3:
          let data1 = null;
          if( typeof(payload.copyPersonal) != Array && payload.copyPersonal == null ){
              const response2 = yield call(constPersonal, payload);
              data1 = response2.data.data;
          }else{
              data1 = payload.copyPersonal;
          }
          if (data1) {
            yield put({
                type: 'modelHandle1',
                payload:{
                  ...payload,
                  copyPersonal:data1
                }
            });
          }
          break;
      }
    },
    *addcard({payload},{call,put}){//新增银行卡
      const response = yield call(addCard, payload);
      if(response && response.data.code === 200){
          message.success("银行卡添加成功",2);
          const response1 = yield call(constCard , payload);
          if(response1 && response1.data.code === 200){
              yield put({
                type: 'updateCard',
                payload:{
                    constCard: response1.data.data
                }
              });
          }
      }else{
        message.error(response.data.message);
      }
    },
    *addconst({payload},{call,put,select}){//添加审批人
      const data = yield select(({ reimBurse }) => reimBurse.constdata );
      if(data.length<5){
        if(payload.type == 1){
          data.push(payload.row);
        }
        //payload.constPersonal.splice(payload.index,1);
        yield put({
            type: 'updateConst',
            payload: {
                constdata : data,
                constPersonal:payload.constPersonal
            }
        });
      }
    },
    *addcopy({payload},{call,put,select}){//添加抄送人
      const  data  = yield select(({ reimBurse }) => reimBurse.copydata );
      if(data.length<5){
        if(payload.type == 1){
          data.push(payload.row);
        }
        //payload.copyPersonal.splice(payload.index,1);
        yield put({
            type: 'updateCopy',
            payload: {
                copydata : data,
                copyPersonal:payload.copyPersonal
            }
        });
      }
    },
    *create({payload},{call,put}){//提交借款单
      const { data } = yield call(constCreate, payload);
      if (data && data.code === 200) {
        yield put({
          type: 'hideModal1'
        });
        let const_data = payload.constdata;
        let copy_data = payload.copydata;
        const_data.length = 0
        copy_data.length = 0;
        console.log(const_data);
        yield put(routerRedux.push({
          pathname: '/success',
          query: {
            apply_id:data.data,
            urltype:payload.urltype,
            constdata:const_data,
            copydata:copy_data
          }
        }));
      } else {
        message.error(data.content, 5);
      }
    },
    *ApplyIDquery({ payload }, { call, put }) {
      const   response = yield call(GetApplyID, {type:payload.type});
      if (response.data && response.data.code == 200 ) {
          yield put({
            type: 'querySuccess',
            payload:{...payload,
              addApplyID:response.data.data.apply_id,
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
      return { ...state,...action.payload, isshowcardmodal:false, isshowconstmodal:false, isshowcopymodal:false ,issubmitmodal:false};
    },
  }
};
