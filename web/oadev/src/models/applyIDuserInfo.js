import { GetApplyID } from '../services/applyIDuserInfo';
import { parse } from 'qs';
import { message} from 'antd';
import { userLogin } from '../components/common';

export default {
  namespace: 'ApplyIDuserInfo',
  state: {
    addApplyID:null,
    departMent:null,
    applyName:null
  },
  subscriptions: {
    setup({ dispatch, history }) {
      history.listen(location => {
        if (location.pathname === '/reimburse' || location.pathname === '/locnment' || location.pathname === '/repayment') {
          dispatch({
            type: 'ApplyIDquery',
            payload:location.query
          });
        }
      });
    },
  },

  effects: {

  },


  reducers: {
    querySuccess(state, action) {
      return { ...state, ...action.payload};
    },
  },

};