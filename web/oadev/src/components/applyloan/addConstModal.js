import { connect } from 'dva';
import _ from 'underscore';
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

            const { constPersonal } = this.props.applyLoan;
            this.props.dispatch({
              type: 'applyLoan/addconst',
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
            type: 'applyLoan/hideModal'
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
          visible:this.props.applyLoan.isshowconstmodal,
          onOk:this.handleonOK,
          onCancel:this.onCancel,
        };

        const { getFieldDecorator,getFieldsValue } = this.props.form;
        const {constPersonal,constdata} = this.props.applyLoan;
        let personalOptions =[],constid = [];
        if(constdata.length>0){constid = constdata.map(data => parseInt(data.id));}
        if(constPersonal != null){
            for(let i=0;i<constPersonal.length;i++){
                if(constdata.length>0){
                    let personid=[];
                    personid.push(constPersonal[i].id);
                    if(_.intersection(constid,personid).length>0){
                        personalOptions.push(<Option key={constPersonal[i].id} disabled>{constPersonal[i].name}</Option>);
                    }else{
                        personalOptions.push(<Option key={constPersonal[i].id}>{constPersonal[i].name}</Option>);
                    }
                }else{
                    personalOptions.push(<Option key={constPersonal[i].id}>{constPersonal[i].name}</Option>);
                }
            }
        }

        return(
                <Modal {...modalOpts} >
                        <Form>
                            <FormItem {...formItemLayout} label={this.props.title} hasFeedback>
                                    {getFieldDecorator('audit_personal', {
                                        rules: [{ required: true, message: '请选择审批人!' }]
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

function mapStateToProps({ applyLoan }) {
  return { applyLoan };
}


export default connect(mapStateToProps)(Form.create()(AddConstModal));