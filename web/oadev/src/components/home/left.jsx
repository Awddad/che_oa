/**
 * Created by tianchuhan on 2016/11/27.
 */
import React, { PropTypes } from 'react';
import { Menu, Icon, Switch,Breadcrumb,Row } from 'antd';
import { Link } from 'dva/router';
import styles from './left.less';
import {chkPms,chkPmsForBlock,chkPmsForInline,chkPmsForInlineBlock} from '../common';

import WebStorage from 'react-webstorage';
const webStorage = new WebStorage(window.localStorage || window.sessionStorage);

const SubMenu = Menu.SubMenu;
let _theme = 'light';

let _keypath = {
  'sub1':'报销管理',
  'sub2':'审批管理',
  'sub3':'财务管理',
  'sub4':'统计分析'
};

const Left = React.createClass({
  getInitialState() {
    return {
      theme:_theme,
      selectedKeys: [],
      adminPms:this.props.adminPms
    };
  },
  changeTheme(value) {
    _theme = value ? 'dark' : 'light';
    this.setState({
      theme: _theme,
    });
  },
  contextTypes :{
    router: React.PropTypes.object
  },
  componentWillReceiveProps() {
    this.getSelectedKeys();
  },
  componentDidMount() {
    this.getSelectedKeys();
  },
  handleClick(item) {
    this.context.router.push(item.key);
    webStorage.setItem("menuKey", item.key);
    if(typeof(item.item.props.children) == "object" && item.item.props.children.props.children == "首页"){
      webStorage.setItem("OneCrumb","首页");
      webStorage.setItem("TwoCrumb", "");
      webStorage.setItem("ThreeCrumb","");
    }else{
      webStorage.setItem("OneCrumb", _keypath[item.keyPath[1]]);
      webStorage.setItem("TwoCrumb", item.item.props.children);
      webStorage.setItem("ThreeCrumb","");
    }
  },
  getSelectedKeys(){
    let key = webStorage.getItem('menuKey');
    this.setState({ selectedKeys: [key]});
  },
  render() {
  const renderStyle = (pmsId,style)=>{
    style = style || {};
    return {...style,display:'block'};
  };

  let oneCrumb = webStorage.getItem('OneCrumb');
  let twoCrumb = webStorage.getItem('TwoCrumb');
  let threeCrumb = webStorage.getItem('ThreeCrumb');
  const crumbs = [];
  if(oneCrumb) crumbs.push(<Breadcrumb.Item key="oneCrumb">{oneCrumb}</Breadcrumb.Item>);
  if(twoCrumb) crumbs.push(<Breadcrumb.Item key="twoCrumb">{twoCrumb}</Breadcrumb.Item>);
  if(threeCrumb) crumbs.push(<Breadcrumb.Item key="threeCrumb">{threeCrumb}</Breadcrumb.Item>);

  const nullItem = (null);
    return (
      <div>
      {/*<Switch
          checked={this.state.theme === 'dark'}
          onChange={this.changeTheme}
          checkedChildren="黑"
          unCheckedChildren="白" />*/}
        {
          /*<Row style={{marginLeft:30}}>
            <Breadcrumb>
            {crumbs}
            </Breadcrumb>
          </Row>*/
        }

      <Menu theme={this.state.theme}
          onClick={this.handleClick}
          defaultOpenKeys={['sub1','sub2','sub3','sub4']}
          defaultSelectedKeys={['/adminhome']}
          selectedKeys={this.state.selectedKeys}
          mode="inline"
          >

          <Menu.Item key="/adminhome" style={chkPmsForBlock(['index'])}><span style={{paddingLeft:24}}>首页</span></Menu.Item>
          {/*chkPms(['shen_qing_bao_xiao','shen_qing_bao_xiao','shen_qing_huang_kuan'])?
                  <SubMenu key="sub1" title={<span><Icon type="solution"/><span>报销管理</span></span>}>
                    <Menu.Item key="/reimburse" style={chkPmsForBlock(['shen_qing_bao_xiao'])}>申请报销</Menu.Item>
                    <Menu.Item key="/applyloan" style={chkPmsForBlock(['shen_qing_jie_kuan'])}>申请借款</Menu.Item>
                    <Menu.Item key="/repayment" style={chkPmsForBlock(['shen_qing_huang_kuan'])}>申请还款</Menu.Item>
                  </SubMenu>
          :null*/}

          {chkPms(['dai_wo_shen_pi','wo_yi_shen_pi','wo_fa_qi_de','chao_song_gei_wo'])?
                  <SubMenu key="sub2" title={<span><Icon type="solution"/><span>审批管理</span></span>}>
                    <Menu.Item key="/waitmeapprove" style={chkPmsForBlock(['dai_wo_shen_pi'])}>待我审批</Menu.Item>
                    <Menu.Item key="/already-approve" style={chkPmsForBlock(['wo_yi_shen_pi'])}>我已审批</Menu.Item>
                    <Menu.Item key="/mysend" style={chkPmsForBlock(['wo_fa_qi_de'])}>我发起的</Menu.Item>
                    <Menu.Item key="/ccsend" style={chkPmsForBlock(['chao_song_gei_wo'])}>抄送给我</Menu.Item>
                  </SubMenu>
          :null}

          {chkPms(['fu_kuan_que_ren','shou_kuan_que_ren'])?
                  <SubMenu key="sub3" title={<span><Icon type="solution"/><span>财务管理</span></span>}>
                    <Menu.Item key="/payment" style={chkPmsForBlock(['fu_kuan_que_ren'])}>付款确认</Menu.Item>
                    <Menu.Item key="/make_collections" style={chkPmsForBlock(['shou_kuan_que_ren'])}>收款确认</Menu.Item>
                  </SubMenu>
          :null}

          {chkPms(['yuan_gong_jie_kuan_ming_xi_biao'])?
                  <SubMenu key="sub4" title={<span><Icon type="solution"/><span>统计分析</span></span>}>
                    <Menu.Item key="/statistics" style={chkPmsForBlock(['yuan_gong_jie_kuan_ming_xi_biao'])}>在借款员工明细表</Menu.Item>
                  </SubMenu>
          :null}

    </Menu>
  </div>
  );
},
});

export default Left;
