//审批
import React,{ Component,PropTypes} from 'react';
import { routerRedux } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Button,Select, Row, Col,message, Steps,Popover,Table,Input,Modal } from 'antd';
import styles from '../../routes/style.less';
import cs from 'classnames';
const Option = Select.Option;
const FormItem = Form.Item;
const confirm = Modal.confirm;

const Approval = React.createClass({
    render(){
        const formItemLayout = {
          labelCol: {
            xs: { span: 24 },
            sm: { span: 3 },
            md: { span: 2 },
          },
          wrapperCol: {
            xs: { span: 24 },
            sm: { span: 14 },
          },
        };

        const { getFieldDecorator } = this.props.form;

        return (
            <div className={styles.postil}>
              <Form>
                <FormItem {...formItemLayout} label="审批备注">
                  {getFieldDecorator('approval', {
                    rules: [{ required: true, message: '请输入审批内容!'}]
                  })(
                    <Input type="text" placeholder="请输入" />
                  )}
                </FormItem>
                <FormItem >
                    <Button className={cs('ant-col-sm-offset-3','ant-col-md-offset-2','mr-md')} type="primary" data-Status="1" onClick={this.props.handlepass} >通过</Button>
                    <Button data-Status="0" onClick={this.props.handlepass}>不通过</Button>
                </FormItem>
              </Form>
            </div>
        );
    }
});

Approval.propTypes = {
   location: PropTypes.object,
   dispatch: PropTypes.func,
};

export default Form.create()(Approval);