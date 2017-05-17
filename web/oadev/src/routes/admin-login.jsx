import React, { PropTypes } from 'react';
import { routerRedux } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Input, Button, Checkbox,Row, Col,Tag,Alert } from 'antd';
import styles from './admin-login.less';
import uuid from 'uuid';

const FormItem = Form.Item;

const AdminLoginForm = Form.create()(React.createClass({
  getInitialState() {
    return this.genCaptcha();
  },
  handleSubmit(e) {
      e.preventDefault();
      const self = this;
      self.props.form.validateFields((errors, values) => {
        if (errors) {
          return;
        }
        const data = self.props.form.getFieldsValue();
        self.props.dispatch({
          type: 'adminLogin/login',
          payload: {
            ...self.state,
            ...data
          }
        });
    });
    self.refreshCaptcha();
  },
  genCaptcha(){
    const uid = uuid.v4();
    return {
      captchaId:uid,
      captcha:`/admin/AdminCtrl/captcha?captchaId=${uid}`
    }
  },
  refreshCaptcha(){
    this.setState(this.genCaptcha());
  },
render() {
  const { getFieldDecorator } = this.props.form;
    return (
      <Row className={styles.admin_login}>
      <Form onSubmit={this.handleSubmit} className={styles.loginForm}>
<FormItem>
<h2 className={styles.title}>汽车违章后台管理系统</h2>
</FormItem>
        <FormItem>
        {getFieldDecorator('username', {
          rules: [
            { required: true, message: '请输入用户名' },
            { pattern:/^[a-zA-Z0-9_]{4,16}$/, message: '账号格式有误(4~16位大小写字母或下划线)'}
          ],
        })(
        <Input maxLength="16" addonBefore={<Icon type="user" />} placeholder="用户名" size="large"/>
        )}
        </FormItem>
        <FormItem>
        {getFieldDecorator('password', {
          rules: [
            { required: true, message: '请输入密码' },
            { pattern:/^[a-zA-Z0-9_]{6,20}$/, message: '密码格式有误(6~20位大小写字母或下划线)'}
          ],
        })(
        <Input maxLength="20" addonBefore={<Icon type="lock" />} type="password" placeholder="密码" size="large"/>
        )}
        </FormItem>
<FormItem>
  <Row gutter={8}>
  <Col span={19}>
{getFieldDecorator('captcha', {
  rules: [{ required: true, message: '请输入验证码' }],
})(
<Input maxLength="4" addonBefore={<Icon type="qrcode" />} type="text" placeholder="验证码" size="large"/>
)}
</Col>
<Col span={5}>
  <img src={this.state.captcha} style={{'width':'56px','height':'32px',"cursor":"pointer"}} onClick={this.refreshCaptcha}/>
</Col>
</Row>
</FormItem>
        <FormItem>
        {/*{getFieldDecorator('remember', {
          valuePropName: 'checked',
          initialValue: true,
        })(
        <Checkbox>记住我</Checkbox>
        )}
        <a className={styles.loginFormForgot}>忘记密码?</a>*/}
        <Button type="primary" htmlType="submit" className={styles.loginFormButton}>
          登录
          </Button>
        </FormItem>
      </Form>
      </Row>
    );
  },
}));

export default connect()(AdminLoginForm);
