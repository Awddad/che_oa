import React from 'react';
import { Router, Route } from 'dva/router';

import Loading from './routes/loading';
import AdminHome from './routes/admin-home';
import reimBurse from './routes/reimBurse';
import ApplyLoan from './routes/applyLoan';
import RepayMent from './routes/repayMent';
import success from './routes/success';
import reimBurseDetails from './routes/detail/reimBurseDetails';
import loanMentDetail from './routes/detail/loanMentDetail';
import repayMentDetail from './routes/detail/repayMentDetail';

import mysend from './routes/mysend';
import ccsend from './routes/ccsend';
import payment from './routes/payment';
import makecollections from './routes/make_collections';
import alreadyapprove from './routes/already-approve';
import Statistics from './routes/statistics';
import WaitmeApprove from './routes/waitme-approve';
import Loginout from './routes/loginout';

function RouterConfig({ history }) {
  return (
    <Router history={history}>
      <Route path="/"  component={Loading} />
      <Route path="/adminhome" breadcrumbName="首页" component={AdminHome} />
      <Route path="/reimburse" breadcrumbName="申请报销" component={reimBurse} />
      <Route path="/applyloan" breadcrumbName="申请借款" component={ApplyLoan} />
      <Route path="/repayment" breadcrumbName="申请还款" component={RepayMent} />
      <Route path="/success" breadcrumbName="申请成功" component={success} />
      <Route path="/mysend" breadcrumbName="我发起的" component={mysend} />
      <Route path="/ccsend" breadcrumbName="抄送给我" component={ccsend} />
      <Route path="/payment" breadcrumbName="付款确认" component={payment} />
      <Route path="/make_collections" breadcrumbName="还款确认" component={makecollections} />
      <Route path="/waitmeapprove" breadcrumbName="待我审批" component={WaitmeApprove} />
      <Route path="/already-approve" breadcrumbName="我已审批" component={alreadyapprove} />
      <Route path="/statistics" breadcrumbName="在职员工借款明细表" component={Statistics} />
      <Route path="/reimbursedetail" breadcrumbName="报销详情" component={reimBurseDetails} />
      <Route path="/loanmentdetail" breadcrumbName="借款详情" component={loanMentDetail} />
      <Route path="/repaymentdetail" breadcrumbName="还款款详情" component={repayMentDetail} />
      <Route path="/loginout" component={Loginout} />
    </Router>
  );
}

export default RouterConfig;
