import { constCard } from '../services/applyLoan';
import { parse } from 'qs';
import { message} from 'antd';
import { routerRedux } from 'dva/router';
import { userLogin } from '../components/common';

export default {
    namespace: 'Confirm',
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
            /*if (location.pathname === '/applyloan') {
              dispatch({
                type: 'query',
                payload: location.query,
              });
            }*/
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
    },
    reducers: {
        refreshstaste(state) {
          return { ...state, loading: true };
        },
    }
}
