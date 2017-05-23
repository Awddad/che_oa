import React, { PropTypes } from 'react';
import { Breadcrumb, Row, Col, Layout } from 'antd';
import { connect } from 'dva';
import styles from './main.less';
import Top from './Top';
import Left from './Left';
import Menu from './Menu';
import Bottom from './Bottom';
import { userLogin } from '../common';
const { Header, Content, Footer, Sider } = Layout;

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
        userLogin();
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
                          <div style={{ padding: 24, background: '#fff', minHeight: 360 }}>
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
