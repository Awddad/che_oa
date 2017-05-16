import { connect } from 'dva';
import React,{ Component,PropTypes} from 'react';
import {Form,Icon,Button,Input,Checkbox,Select,Upload,Row,Col,Modal } from 'antd';
import styles from '../../routes/reimburse.less';
import cs from 'classnames';
const FormItem = Form.Item;
const Option = Select.Option;

const AddConstModal = React.createClass({
    handleonOK(){
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
            const constdata = { ...getFieldsValue() };
            const name = (constdata.audit_personal.label).split("-")[0];
            const row = {'id':constdata.audit_personal.key,'name':name};

            this.props.dispatch({
              type: 'applyLoan/addconst',
              payload: {
                row:row,
                type:1
                }
            });
        });
    },
    onCancel(){
        this.props.dispatch({
            type: 'applyLoan/hideModal'
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
          key:this.props.key,
          title:this.props.title,
          visible:this.props.applyLoan.isshowconstmodal,
          onOk:this.handleonOK,
          onCancel:this.onCancel,
        };

        const { getFieldDecorator,getFieldsValue } = this.props.form;
        const {constPersonal} = this.props.applyLoan;
        const  personalOptions = constPersonal.map(data =><Option key={data.id}>{data.name}</Option>);
        //const  personalOptions = [];

        return(
                <Modal {...modalOpts} >
                        <Form>
                            <FormItem {...formItemLayout} label={this.props.title} hasFeedback>
                                    {getFieldDecorator('audit_personal', {
                                        rules: [{ required: true, message: '请选择审核人!' }]
                                    })(
                                        <Select className="t-l" labelInValue placeholder="请选择" size="large" style={{ width: '100%' }}>
                                            {personalOptions}
                                        </Select>
                                    )}
                            </FormItem>
                        </Form>
                </Modal>
        );
    }
});

AddConstModal.propTypes = {
    location: PropTypes.object,
    form: PropTypes.object,
    dispatch: PropTypes.func
}

function mapStateToProps({ applyLoan }) {
  return { applyLoan };
}


export default connect(mapStateToProps)(Form.create()(AddConstModal));