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
            const name = (constdata.audit_personal.label).split(" ")[0];
            const row = {'id':constdata.audit_personal.key,'name':name};
            const { constPersonal } = this.props.reimBurse;
            this.props.dispatch({
              type: 'reimBurse/addconst',
              payload: {
                    row:row,
                    type:1,
                    constPersonal:constPersonal
                }
            });
        });
    },
    onCancel(){
        this.props.dispatch({
            type: 'reimBurse/hideModal'
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
        const {constPersonal} = this.props.reimBurse;
        const  personalOptions = constPersonal.map(data =><Option key={data.id}>{data.name}</Option>);

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

function mapStateToProps({ reimBurse }) {
  return { reimBurse };
}


export default connect(mapStateToProps)(Form.create()(AddConstModal));