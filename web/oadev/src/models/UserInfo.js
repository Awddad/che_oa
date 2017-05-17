import { UserInfo } from '../services/UserInfo';
import { parse } from 'qs';
import { message} from 'antd';

export default {
    namespace: 'UserInfo',
    state: {
        userInfo:{},
        personID:null
    },
    subscriptions: {
        setup({ dispatch, history }) {
          history.listen(location => {
            if (location.pathname === '/mysend' || location.pathname === '/reimbursedetail' || location.pathname === '/loanmentdetail' || location.pathname === '/repaymentdetail') {
              dispatch({
                type: 'query',
              });
            }
          });
        },
    },
    effects: {
        *query({ payload }, { call, put }) {
          const { data } = yield call(UserInfo);
          if (data && data.code == 200) {
            yield put({
              type: 'querySuccess',
              payload: {
                    userInfo:data.data,
                    personID:data.data.userinfo.person_id
              },
            });
          }
        },
        reducers: {
            querySuccess(state, action) {
              return { ...state, ...action.payload};
            }
        },
    }
}
