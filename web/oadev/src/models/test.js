import { query,BaoxiaoDetail } from '../services/success';
import { parse } from 'qs';
import { message } from 'antd';
import { routerRedux } from 'dva/router';
import pathToRegexp from 'path-to-regexp';


export default {
    namespace: 'test',
    state: {
        urltype:null,
        Detail:null
    },
    subscriptions: {
        setup({ dispatch, history }) {
          history.listen(location => {
            if (location.pathname === '/test') {
                userLogin();
              dispatch({
                type: 'query',
                payload: {
                  urltype : location.query.urltype,
                  apply_id: location.query.apply_id
                }
              });
            }
          });
        },
    },
    effects: {
        *query({ payload }, { call, put }) {
            let response = null;
            response = yield call(BaoxiaoDetail, {'apply_id' : '2017051219002201327'});
            if (response.data && response.data.code === 200) {
                yield put({
                    type: 'querySuccess',
                    payload: {
                        urltype:payload.urltype,
                        Detail:response.data.data
                    }
                });
            } else {
                message.error(response.data.message, 3);
            }
        }
    },
    reducers: {
        querySuccess(state, action) {
          return { ...state, ...action.payload};
        },
    }
}