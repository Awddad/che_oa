import { connect } from 'dva';
import React,{ Component,PropTypes} from 'react';
import {Form,Icon,Button,Input,Checkbox,Select,Upload,Row,Col,Modal } from 'antd';
import styles from '../../routes/reimburse.less';
import cs from 'classnames';
const FormItem = Form.Item;
const Option = Select.Option;

const AddConstModal = React.createClass({
    getInitialState(){
        return {
          index:null
        };
    },
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
            const name = (constdata.audit_personal.label).split(" ")[0];
            const row = {'id':constdata.audit_personal.key,'name':name};
            const { constPersonal } = this.props.repayMent;
            this.props.dispatch({
                type: 'repayMent/addconst',
                payload: {
                    row:row,
                    type:1,
                    index:this.state.index,
                    constPersonal:constPersonal
                }
            });
        });
    },
    hanleSelect(value, option){
        this.setState({
            index:option.props.index
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
            sm: { span: 4 },
          },
          wrapperCol: {
            xs: { span: 24 },
            sm: { span: 18 },
          },
        };

        const modalOpts = {
          key:this.props.key,
          title:this.props.title,
          width:600,
          visible:this.props.isshowconstmodal,
          onOk:this.handleonOK,
          onCancel:this.onCancel,
        };

        const { getFieldDecorator,getFieldsValue } = this.props.form;
        const {constPersonal} = this.props.repayMent;
        let personalOptions ="";
        if(constPersonal != null){
            personalOptions = constPersonal.map(data => <Option key={data.id}>{data.name}</Option>);
        }

        return(
                <Modal {...modalOpts} >
                        <Form>
                            <FormItem {...formItemLayout} label={this.props.title} hasFeedback>
                                    {getFieldDecorator('audit_personal', {
                                        rules: [{ required: true, message: '请选择审核人!' }]
                                    })(
                                        <Select className="t-l" labelInValue placeholder="请选择" size="large" style={{ width: '100%' }} onSelect = {this.hanleSelect}>
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

function mapStateToProps({ repayMent }) {
  return { repayMent };
}


export default connect(mapStateToProps)(Form.create()(AddConstModal));