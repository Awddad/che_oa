import { connect } from 'dva';
import React,{ Component,PropTypes} from 'react';
import {Form,Icon,Button,Input,Checkbox,Select,Upload,Row,Col,Modal } from 'antd';
import styles from '../../routes/reimburse.less';
import cs from 'classnames';
const FormItem = Form.Item;
const Option = Select.Option;

const AddCardModal = React.createClass({
    onOK(e){
        e.preventDefault();
        const {
              getFieldDecorator,
              validateFields,
              getFieldsValue,
              getFieldValue
        } = this.props.form;


        validateFields((errors) => {
            if (errors) {
                return;
            }
            const carddata = { ...getFieldsValue() };
            const row = {bank_name:carddata.bank,card_id:carddata.code,bank_des:carddata.open_account}
            this.props.dispatch({
              type: 'repayMent/addcard',
              payload: row
            });
        });
    },
    onCancel(){
        this.props.dispatch({
            type: 'repayMent/hideModal'
        });
    },
    render(){
        const formItemLayout = {
          labelCol: {
            xs: { span: 24 },
            sm: { span: 6 },
          },
          wrapperCol: {
            xs: { span: 24 },
            sm: { span: 14 },
          },
        };

        const modalOpts = {
          visible:this.props.isshowcardmodal,
          onOk: this.onOK,
          onCancel: this.onCancel,
        };

        const { getFieldDecorator } = this.props.form;
        return(
                <Modal title="增加银行卡号"  {...modalOpts} >
                        <Form>
                            <FormItem {...formItemLayout} label="银行" hasFeedback>
                                {getFieldDecorator('bank', {
                                    rules: [{required: true, message: '请输入银行名称!',}]
                                })(
                                    <Input placeholder="请输入" />
                                )}
                            </FormItem>
                            <FormItem {...formItemLayout} label="卡号" hasFeedback>
                                {getFieldDecorator('code', {
                                    rules: [
                                      {required: true, message: '请输入卡号!'}
                                    ]
                                })(
                                    <Input placeholder="请输入" />
                                )}
                            </FormItem>
                            <FormItem {...formItemLayout} label="开户行">
                                {getFieldDecorator('open_account')(
                                    <Input  placeholder="请输入"/>
                                )}
                            </FormItem>
                        </Form>
                </Modal>
        );
    }
});

AddCardModal.propTypes = {
    location: PropTypes.object,
    form: PropTypes.object,
    dispatch: PropTypes.func
}

function mapStateToProps({ repayMent }) {
  return { repayMent };
}

export default connect(mapStateToProps)(Form.create()(AddCardModal));