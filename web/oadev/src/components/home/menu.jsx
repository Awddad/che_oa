import React, { PropTypes } from 'react';
import { Menu, Icon, Switch } from 'antd';
import { Link } from 'dva/router';

const LeftMenu = React.createClass({
  getInitialState() {//初始化
    return {
      selectedKeys: []
    };
  },

  contextTypes :{
    router: React.PropTypes.object
  },

  componentWillReceiveProps() {
    this.setState({ selectedKeys: [this.props.location.pathname] });
  },

  componentDidMount() {
    this.setState({ selectedKeys: [this.props.location.pathname] });
  },

  linkTo(item) {
    this.context.router.push(item.key);
  },

  render() {
    return (
        <Menu mode="inline" theme="dark" defaultSelectedKeys={['/role']} selectedKeys={this.state.selectedKeys} onClick={this.linkTo}>
          <Menu.Item key="/role">角色管理</Menu.Item>
          <Menu.Item key="/product">产品列表设置</Menu.Item>
          <Menu.Item key="/document">上传资料设置</Menu.Item>
        </Menu>
    );
  }
});

export default LeftMenu;
