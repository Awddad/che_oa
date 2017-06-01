/**
 * Created by tianchuhan on 2016/11/27.
 */
import React, { PropTypes } from 'react';
import { connect } from 'dva';
import { Row, Icon, Menu, Dropdown } from 'antd';
import { Link } from 'dva/router';
import styles from './top.less';
import cs from 'classnames';
import { getLocalStorage } from '../common';


const menu = (
  <Menu>
    <Menu.Item>
      <Link rel="noopener noreferrer" to="/loginout" className="t-c">退出</Link>
    </Menu.Item>
  </Menu>
);

const Top = React.createClass({
    getInitialState(){
        return {
            is_sidebar:false
        }
    },
    loginout(){
        this.props.dispatch({
            type:"adminHome/loginout"
        });
    },
    render(){
        let username = localStorage.getItem("username");
        if(username == null){
            this.props.dispatch({
                type:"Loading/userinfo"
            });
        }
        return (
          	<Row>
                <div className={styles.menu}>
                    <Icon className={cs("trigger")} type={this.props.collapsed ? 'menu-unfold' : 'menu-fold'}  onClick={this.props.toggle} />
                    <div className={styles.sec_right}>{/*<a className={styles.reset}>修改密码</a>*/}
                        <Dropdown overlay={menu}>
                      	    <a className="ant-dropdown-link" href="javascript:void(0);" style={{color:'#fff'}}>
                      	        <Icon type="user" style={{fontSize:16,color:'#eeeeee'}}/> { getLocalStorage('username') } <Icon type="down" style={{paddingLeft:5}}/>
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
  dispatch: PropTypes.func,
  adminHome:PropTypes.object,
  Loading:PropTypes.object,
};

function mapStateToProps({ adminHome,Loading}) {
  return { adminHome,Loading };
}

export default connect(mapStateToProps)(Top);
