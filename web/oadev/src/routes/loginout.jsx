import React, { PropTypes } from 'react';
import { Breadcrumb, Row, Col } from 'antd';
import { connect } from 'dva';

const Loginout = React.createClass({
    render(){
        this.props.dispatch({
            type: 'adminHome/loginout'
        });
        return(
            <div></div>
        )
    }
})

Loginout.PropTypes = {
    location: PropTypes.object,
    dispatch: PropTypes.func,
    adminHome:PropTypes.object,
};

function mapStateToProps({ adminHome}) {
  return { adminHome };
}

export default connect(mapStateToProps)(Loginout);