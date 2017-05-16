import { connect } from 'dva';
import React from 'react';
import {Icon} from 'antd';


const Pagetitle = React.createClass({
  handlelocation(){
    location.href = history.go(-1);
  },
  render(){
    let icon;
    if(this.props.isback){
      icon = (<Icon type="left" />);
    }
    return(
      <h1 className='page-title'>
        <a onClick={this.handlelocation}>{icon}</a>
        <strong>{this.props.title}</strong>
      </h1>
    )
  }
});

export default  connect()(Pagetitle);