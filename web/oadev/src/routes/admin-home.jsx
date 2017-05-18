import React, { PropTypes } from 'react';
import { routerRedux,Link } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Button, Row, Col,message} from 'antd';
import cs from 'classnames'
import Main from '../components/home/main';
import styles from './admin-home.less';

import {chkPms,chkPmsForBlock,chkPmsForInline,chkPmsForInlineBlock} from '../components/common';

const FormItem = Form.Item;

function AdminHome({ location, dispatch, adminHome }) {
  const {
    data,
    loanApply,
    loading,
    modalVisible,
    personID,
    userInfo
  } = adminHome;


  return (
  	<Main location={location}>
  		<Row>
	       <div className={styles.home_wrap}>
                <h2 className={styles.mb_md}>报销相关</h2>
                <Row className="home-wraplist">
                    <ul className="ant-col-md-24 ant-col-sm-24">
                      <li className="ant-col-md-6"><Link to="/reimBurse" className={styles.reimBurse}>申请报销</Link></li>
                      <li className="ant-col-md-6"><Link to="/applyloan" className={styles.applyloan}>申请借款</Link></li>
                      <li className="ant-col-md-6"><Link to="/repayment" className={styles.repayment}>申请还款</Link></li>
                    </ul>
                </Row>
                <div className={styles.home_office}>
                    <h2 className={styles.mb_md}>办公物品相关</h2>
                    <Row className="home-wraplist">
                        <ul className="ant-col-md-24 ant-col-sm-24">
                            <li className="ant-col-md-6"><a className={styles.demand}>发起采购需求</a></li>
                            <li className="ant-col-md-6"><a className={styles.purchase}>申请采购</a></li>
                            <li className="ant-col-md-6"><a className={styles.Release}>固定资产发放</a></li>
                            <li className="ant-col-md-6"><a className={styles.Recover}>固定资产收回</a></li>
                        </ul>
                    </Row>
                </div>
            </div>
     	</Row>
    </Main>
	);
}

AdminHome.propTypes = {
  location: PropTypes.object,
  UserInfo: PropTypes.object,
  adminHome:PropTypes.object,
};

function mapStateToProps({ adminHome}) {
  return { adminHome };
}

export default connect(mapStateToProps)(AdminHome);

