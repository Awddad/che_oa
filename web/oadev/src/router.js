import React from 'react';
import { Router, Route } from 'dva/router';

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


function RouterConfig({ history }) {
  return (
    <Router history={history}>
      <Route path="/" component={AdminHome} />
      <Route path="/reimburse" component={reimBurse} />
      <Route path="/applyloan" component={ApplyLoan} />
      <Route path="/repayment" component={RepayMent} />
      <Route path="/success" component={success} />
      <Route path="/mysend" component={mysend} />
      <Route path="/ccsend" component={ccsend} />
      <Route path="/payment" component={payment} />
      <Route path="/make_collections" component={makecollections} />
      <Route path="/already-approve" component={alreadyapprove} />
      <Route path="/reimbursedetail" component={reimBurseDetails} />
      <Route path="/loanmentdetail" component={loanMentDetail} />
      <Route path="/repaymentdetail" component={repayMentDetail} />
      <Route path="/mysend" component={mysend} />
      <Route path="/ccsend" component={ccsend} />
      <Route path="/payment" component={payment} />
      <Route path="/make_collections" component={makecollections} />
      <Route path="/already-approve" component={alreadyapprove} />
      <Route path="/statistics" component={Statistics} />
      <Route path="/waitmeapprove" component={WaitmeApprove} />
    </Router>
  );
}

export default RouterConfig;
