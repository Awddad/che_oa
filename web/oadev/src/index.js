import './index.html';
import './index.css';
import dva from 'dva';
import { routerRedux } from 'dva/router';
import createLoading from 'dva-loading';
import { message} from 'antd';

// 1. Initialize
const app = dva({
  onError(error,dispatch) {
    if(error.response && error.response.status==401){
      error.response.json().then(function(data) {
        message.error('登录超时，请重新登录');
        setTimeout(function(){
          dispatch(routerRedux.push({
            pathname: data.exceptionCode == 100?"/admin":"/login"
          }));
        },1500);
      });
    }else if(error.response && error.response.status==403){
      //message.destroy();
      message.error('没有 '+error.response.url+' 权限，请联系管理员',4);
    }else{
      console.log(error);
      message.error('系统错误,请联系管理员',4);
    }
  },
});

// 2. Plugins
// app.use({});
app.use(createLoading());

// 3. Model
app.model(require('./models/adminLogin'));
app.model(require('./models/adminHome'));
app.model(require('./models/reimBurse'));
app.model(require('./models/applyLoan'));
app.model(require('./models/repayMent'));
app.model(require('./models/success'));
app.model(require('./models/already-approve'));
app.model(require('./models/mysend'));
app.model(require('./models/ccsend'));
app.model(require('./models/payment'));
app.model(require('./models/make_collections'));
app.model(require('./models/detail'));
app.model(require('./models/statistics'));
app.model(require('./models/waitme-approve'));


// 4. Router
app.router(require('./router'));

// 5. Start
app.start('#root');
