import React, { PropTypes } from 'react';
import { routerRedux,Link } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Button, Row, Col,message} from 'antd';
import cs from 'classnames'
import Main from '../components/home/main';
import styles from './admin-home.less';

import {chkPms,chkPmsForBlock,chkPmsForInline,chkPmsForInlineBlock} from '../components/common';

const FormItem = Form.Item;

const AdminHome = React.createClass({
    render(){
        const {
            data,
            loanApply,
            loading,
            modalVisible,
            homeshowpage
        } = this.props.adminHome;

        return (
            <Main location={location}>
              <Row>
                <div className={styles.home_wrap}>
                    <h2 className={styles.mb_md}>报销相关</h2>
                    <Row className="home-wraplist">
                      <ul className="ant-col-md-12 ant-col-sm-24">
                          <li className="ant-col-md-8"><Link to="/reimBurse">申请报销</Link></li>
                          <li className="ant-col-md-8"><Link to="/applyloan">申请借款</Link></li>
                          <li className="ant-col-md-8"><Link to="/repayment">申请还款</Link></li>
                      </ul>
                    </Row>
                </div>
                <div style={{'display':'none'}}>
                    <h2>办公物品相关</h2>
                    <ul>
                        <li><a>申请报销</a></li>
                        <li><a>申请借款</a></li>
                        <li><a>申请还款</a></li>
                    </ul>
                </div>
              </Row>
            </Main>
        );
    }
});

AdminHome.propTypes = {
  location: PropTypes.object,
  userinfo: PropTypes.object,
  dispatch: PropTypes.func,
  adminHome:PropTypes.object,
};

function mapStateToProps({ adminHome}) {
  return { adminHome };
}

export default connect(mapStateToProps)(AdminHome);

