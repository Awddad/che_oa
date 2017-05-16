import { constType,constCard,constPersonal,constCreate,addCard,GetApplyID,GetUserInfo } from '../services/reimBurse';
import { parse } from 'qs';
import { message} from 'antd';
import { routerRedux } from 'dva/router';

export default {
  namespace: 'reimBurse',
  state: {
    constCard:[],//初始化银行卡信息
    constType:[],//报销类型
    constPersonal:[],//初始化审核/抄送联系人
    tabledata:[],//新增的报销明细
    carddata:[],//新增的银行卡
    constdata:[],//审批人
    initialconstdata:[],
    copydata:[],//抄送人
    bxtypeID:[],//报销明细记录ID
    CardDetail:{},//提交申请返回的必填字段数据
    fileList:{},//上传文件数据
    pics:{},//上传图片数据
    bank_name:null,
    bank_id:null,
    addApplyID:null,//报销单ID
    department:null,
    bxname:null,
    loading: false,
    isshowtablemodal:false,
    isshowcardmodal:false,
    isshowconstmodal:false,
    isshowcopymodal:false,
    issubmitmodal:false
  },

  subscriptions: {
    setup({ dispatch, history }) {
      history.listen(location => {
        if (location.pathname === '/reimburse') {
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
      let response =null;
      switch(payload.modalIndex){
         case 0:
            yield put({
                type: 'modelHandle1',
                payload:{...payload}
            });
          break;
        case 1:
          response = yield call(constType, payload);
          if (response.data.data) {
            yield put({
                type: 'modelHandle1',
                payload:{...payload,constType: response.data.data}
            });
          }
          break;
        case 2:
          let data = null;
          if(payload.constPersonal == null || payload.constPersonal.length == 0){
              response = yield call(constPersonal, payload);
              data = response.data.data;
          }else{
              data = payload.constPersonal;
          }
          console.log(data);
          if (data) {
            yield put({
                type: 'modelHandle1',
                payload:{
                  ...payload,
                  constPersonal: data
                }
            });
          }
          break;
      }
    },
    *table_del_modelHandle({ payload }, { call, put }) {//表格数据删除刷新
      yield put({
          type: 'table_del_modelHandle1',
          payload:{
            tabledata : payload
          }
      });
    },
    *addtable({payload},{call,put,select}){//新增报销明细
      const data = yield select(({ reimBurse }) => reimBurse.tabledata);
      data.push(payload.row);
      yield put({
          type: 'updateTable',
          payload: {
              tabledata : data,
              bxtypeID:payload.bxtypeID
          }
      });
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
      let index =null;
      for(let i =0;i<Object.keys(payload.constPersonal).length;i++ )
      {
        //console.log(payload.row.id +','+Object.values(payload.constPersonal)[i].id);
        if(payload.row.id == Object.values(payload.constPersonal)[i].id ){
          index = i;
        }
      }
      let arr = Object.values(payload.constPersonal);
          arr.splice(index,1)
      yield put({
          type: 'updateConst',
          payload: {
              constdata : data,
              constPersonal:arr
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
        yield put({
            type: 'hideModal1',
        });
        yield put(routerRedux.push({
          pathname: '/success',
          query: {
            apply_id:data.data,
            urltype:payload.urltype
          }
        }));
      } else {
        message.error(data.message, 5);
      }
    },
    *ApplyIDquery({ payload }, { call, put }) {
      const  response1  = yield call(GetApplyID, {type:payload.type});
      const  response2 = yield call(GetUserInfo,{});
      if (response1.data && response2.data && response1.data.code == 200 && response2.data.code == 200 ) {
          yield put({
            type: 'querySuccess',
            payload:{...payload,
              addApplyID:response1.data.data.apply_id,
              bxname:response2.data.data.userinfo.person_name,
              department:response2.data.data.userinfo.org_full_name
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
    updateTable(state,action){
      return { ...state, ...action.payload,isshowtablemodal: false};
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
      return { ...state,...action.payload, isshowtablemodal: false, isshowcardmodal:false, isshowconstmodal:false, isshowcopymodal:false ,issubmitmodal:false};
    },
  }
};
