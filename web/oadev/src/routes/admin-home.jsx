import React, { PropTypes } from 'react';
import { routerRedux,Link } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Button, Row, Col,message} from 'antd';
import cs from 'classnames'
import Main from '../components/home/main';
import styles from './admin-home.less';
import {chkPms,chkPmsForBlock,currentPage} from '../components/common';
import BreadcrumbCustom from '../components/BreadcrumbCustom';
import WebStorage from 'react-webstorage';
const webStorage = new WebStorage(window.localStorage || window.sessionStorage);

const FormItem = Form.Item;

const AdminHome = React.createClass({
    getInitialState(){
        return{}
    },
    contextTypes: {
      router: React.PropTypes.object
    },
    currentPage(e){
          let item = "/"+e.target.getAttribute("href").split("/")[1];
          webStorage.setItem("menuKey", item);
    },
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
                <BreadcrumbCustom first="" second="" furl="" />
                <Row>
                    {chkPms(['shen_qing_bao_xiao','shen_qing_jie_kuan','shen_qing_huang_kuan']) ?
                      (<div className={styles.home_wrap}>
                          <h3 className={cs("mt-md","mb-md")}>报销相关</h3>
                          <Row className="home-wraplist">
                            <ul className="ant-col-sm-24">
                                <li style={chkPmsForBlock(['shen_qing_bao_xiao'])}><Link className={styles.reimBurse} to="/reimburse" onClick = {this.currentPage}>申请报销</Link></li>
                                <li style={chkPmsForBlock(['shen_qing_jie_kuan'])}><Link className={styles.applyloan} to="/applyloan" onClick = {this.currentPage}>申请借款</Link></li>
                                <li className="repayment" style={chkPmsForBlock(['shen_qing_huang_kuan'])}><Link className={styles.repayment} to="/repayment" onClick = {this.currentPage}>申请还款</Link></li>
                            </ul>
                          </Row>
                      </div>)
                      : (<h1>欢迎进入OA系统!</h1>)
                    }
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
  router: React.PropTypes.object
};

function mapStateToProps({ adminHome}) {
  return { adminHome };
}

export default connect(mapStateToProps)(AdminHome);

