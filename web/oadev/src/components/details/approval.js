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
    handlepass(event){
        const ApplyID = this.props.ApplyID;
        const {
            getFieldDecorator,
            validateFields,
            getFieldsValue,
            getFieldValue
        } = this.props.form;

        const formdata = {...getFieldsValue };
        validateFields((errors) => {
            if (errors) {
                return;
            }else{
                let status = event.target.getAttribute("data-Status") == null ? event.target.parentNode.getAttribute("data-Status") : event.target.getAttribute("data-Status");
                 let content=null;
                switch(status){
                    case '0':
                        content = (<p><Icon type="close-circle" style={{color:'#FF6600'}} />通过该申请<br/>确定通过该用户的申请吗？</p>);
                        confirm({
                            title: '确认不通过该申请吗？',
                            content: '确认不通过后，会中止该申请的继续进行并通知申请人。',
                            iconType:'close-circle',
                            onOk() {
                                this.props.dispatch({
                                    type:'Detail/Approval',
                                    payload:{
                                        apply_id:ApplyID,
                                        person_id:'',
                                        des:formdata.approval,
                                        status:status,
                                        url:this.props.url
                                    }
                                });
                            }
                        });
                    break;
                    case '1':
                        content = (<p><Icon type="heck-circle" style={{color:'#00A854'}} />通过该申请<br/>确定通过该用户的申请吗？</p>);
                        confirm({
                            title: '通过该申请',
                            content: '确定通过该用户的申请吗？',
                            onOk() {
                                this.props.dispatch({
                                    type:'Detail/Approval',
                                    payload:{
                                        apply_id:ApplyID,
                                        person_id:'',
                                        des:formdata.approval,
                                        status:status,
                                        url:this.props.url
                                    }
                                });
                            }
                        });
                    break;
                }

            }
        });
    },
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
                    <Button className={cs('ant-col-sm-offset-3','ant-col-md-offset-2','mr-md')} type="primary" data-Status="1" onClick={this.handlepass} >通过</Button>
                    <Button data-Status="0" onClick={this.handlepass}>不通过</Button>
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