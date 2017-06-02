import React, { PropTypes } from 'react';
import { Breadcrumb, Row, Col, Layout,Icon } from 'antd';
import { connect } from 'dva';
import { Link } from 'react-router';
import styles from './main.less';
import Top from './Top';
import Left from './Left';
import Menu from './Menu';
import Bottom from './Bottom';
import Bread from './Bread';
import { userLogin } from '../common';
const { Header, Content, Footer, Sider } = Layout;
import WebStorage from 'react-webstorage';
const webStorage = new WebStorage(window.localStorage || window.sessionStorage);


class Main extends React.Component {
    state = {
        collapsed: false,
    }
    toggle = () => {
        this.setState({
          collapsed: !this.state.collapsed,
        });
    }
    responsive = (collapsed, type) =>{
        this.setState({
          collapsed: !this.state.collapsed,
        });
    }
    render(){
          let oneCrumb = webStorage.getItem('OneCrumb');
          let twoCrumb = webStorage.getItem('TwoCrumb');
          let threeCrumb = webStorage.getItem('ThreeCrumb');
          let oneUrl = webStorage.getItem('OneUrl');
          let twoUrl = webStorage.getItem('TwoUrl');
          const crumbs = [];
          if(oneCrumb && oneUrl != null){
                crumbs.push(<Breadcrumb.Item key="oneCrumb" href={"#"+oneUrl}>{oneCrumb}</Breadcrumb.Item>)
          }else{
                crumbs.push(<Breadcrumb.Item key="oneCrumb" >{oneCrumb}</Breadcrumb.Item>)
          }

          if(twoCrumb && twoUrl != null){
                crumbs.push(<Breadcrumb.Item key="twoCrumb" href={"#"+twoUrl}>{twoCrumb}</Breadcrumb.Item>)
          }else{
                crumbs.push(<Breadcrumb.Item key="twoCrumb" >{twoCrumb}</Breadcrumb.Item>)
          };
          if(threeCrumb) crumbs.push(<Breadcrumb.Item key="threeCrumb">{threeCrumb}</Breadcrumb.Item>);

        const children = this.props.children;
        return (
                <Layout className="warpper">
                    <Sider breakpoint="sm" collapsedWidth="0" trigger={null} collapsible={false} onCollapse={this.responsive} collapsed={this.state.collapsed} >
                        <div className={styles.logo}><h1>车城OA系统</h1></div>
                        <Left location={location}/>
                    </Sider>
                    <Layout>
                        <Header style={{ background: '#fff', padding: 0 }} >
                            <Top location={location}  toggle={this.toggle} collapsed = {this.state.collapsed} />
                        </Header>
                        <Content style={{ margin: '24px 16px 0' }}>
                            <div className="main_content">
                                {/*<Row >
                                    <Breadcrumb>
                                      <Breadcrumb.Item href=""><Icon type="home" /></Breadcrumb.Item>
                                      {crumbs}
                                    </Breadcrumb>
                                </Row>*/}
                                {children}
                            </div>
                        </Content>
                        <Footer style={{ textAlign: 'center' }}>
                          <Bottom location={location} />
                        </Footer>
                    </Layout>
                </Layout>
        );
    }
};

Main.propTypes = {
  children: PropTypes.element.isRequired,
  location: PropTypes.object,
};


export default Main;
