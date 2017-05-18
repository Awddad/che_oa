/**
 * Created by tianchuhan on 2016/11/27.
 */
import React, { PropTypes } from 'react';
import { connect } from 'dva';
import { Row, Icon, Menu, Dropdown } from 'antd';
import { Link } from 'dva/router';
import styles from './top.less';
import WebStorage from 'react-webstorage';
const webStorage = new WebStorage(window.sessionStorage || window.localStorage);

const menu = (
  <Menu>
    <Menu.Item>
      <a rel="noopener noreferrer" to="/oa_v1/default/login-out">退出</a>
    </Menu.Item>
  </Menu>
);
const Top = React.createClass({
    render(){
        return (
          	<Row>
                <div className={styles.menu}>
                  <div className={styles.sec_right}><a className={styles.reset}>修改密码</a>
                   <Dropdown overlay={menu}>
              	    <a className="ant-dropdown-link" href="javascript:void(0);" style={{color:'#fff'}}>
              	     <Icon type="user" style={{fontSize:16,color:'#eeeeee'}}/> {webStorage.getItem('name')} <Icon type="down" style={{paddingLeft:5}}/>
              	    </a>
              	  </Dropdown>
                  </div>
              	</div>
            </Row>
        );
    }
});

Top.PropTypes = {
  location: PropTypes.object,
  userinfo: PropTypes.object,
};

function mapStateToProps({ userinfo }) {
  return { userinfo };
}

export default connect(mapStateToProps)(Top);
