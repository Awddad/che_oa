import './index.html';
import './index.css';
import 'babel-polyfill'
import dva from 'dva';
import createLoading from 'dva-loading';
import { routerRedux,browserHistory } from 'dva/router';
import { message} from 'antd';

// 1. Initialize
const app = dva({
  onError(error,dispatch) {
    if(error.response && error.response.code==401){
        message.error('登录超时，请重新登录');
        setTimeout(function(){
          window.location.href=error.response.data.login_url;
        },800);
    }else if(error.response && error.response.code==403){
      message.error('没有 '+error.response.url+' 权限，请联系管理员',4);
    }else{
      console.log(error);
      message.error('系统错误,请联系管理员',4);
    }
  },
});

/*var opts = {};
opts.effects = function(){
  console.log(123121321321);
};*/
// 2. Plugins
// app.use({});
app.use(createLoading());

// 3. Model
app.model(require('./models/Loading'));
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
app.model(require('./models/UserInfo'));

// 4. Router
app.router(require('./router'));

// 5. Start
app.start('#root');
