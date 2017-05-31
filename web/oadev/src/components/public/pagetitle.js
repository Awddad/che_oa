import { connect } from 'dva';
import React from 'react';
import {Icon} from 'antd';
import cs from 'classnames';


const Pagetitle = React.createClass({
  handlelocation(){
    location.href = history.go(-1);
  },
  render(){
    let icon;
    if(this.props.isback){
      icon = (<Icon type="left" />);
    }
    return (
      <h3 className={cs("mt-md","mb-md")} >{this.props.title}</h3>
    )
  }
});

export default  connect()(Pagetitle);