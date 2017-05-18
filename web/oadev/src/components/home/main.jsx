import React, { PropTypes } from 'react';
import { Breadcrumb, Row, Col } from 'antd';
import { connect } from 'dva';
import styles from './main.less';
import Top from './Top';
import Left from './Left';
import Menu from './Menu';
import Bottom from './Bottom';

function Main({ children, location,showpage }) {
    function menu_left(){
        const menu_lefth = document.body.clientHeight - 50;
        return menu_lefth;
    }
    const style = {'display': showpage ? 'block':'none'}
    return (
                <div className={styles.defaultbc} >
                  <Row className={styles.Top_menu}><Top location={location} /></Row>
                  <Row >
                    <div className={styles.logo}><h1>车城OA系统</h1></div>
                    <Col className={styles.menu_left}>
                      <Left location={location}/>
                    </Col>
                    <Col className={styles.content_right} style={{'minHeight':menu_left()}}>
                        <Row className={styles.content}>
                          {children}
                        </Row>
                        <Row className={styles.footer}><Bottom location={location} /></Row>
                    </Col>
                  </Row>
                </div>
        );

};

Main.propTypes = {
  children: PropTypes.element.isRequired,
  location: PropTypes.object,
};

export default Main;
