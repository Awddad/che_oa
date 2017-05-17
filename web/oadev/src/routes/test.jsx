import React, { PropTypes } from 'react';
import { routerRedux,Link } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Button, Row, Col,message} from 'antd';
import cs from 'classnames'
import Main from '../components/home/main';


const Test = React.createClass({
    const {userInfo} = this.props.UserInfo;
    render(){
        return(

        );
    }
});

Test.propTypes = {
  location: PropTypes.object,
  UserInfo: PropTypes.object,
  test:PropTypes.object,
};

function mapStateToProps({ test,UserInfo }) {
  return { test,UserInfo };
}

export default connect(mapStateToProps)(Test);