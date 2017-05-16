import { connect } from 'dva';
import React,{ Component,PropTypes} from 'react';
import {Form,Icon,Button,Input,Checkbox,Select,Upload,Row,Col,Modal } from 'antd';
import styles from '../../routes/reimburse.less';
import cs from 'classnames';
const FormItem = Form.Item;
const Option = Select.Option;

const AddCopyModal = React.createClass({
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

            const copydata = { ...getFieldsValue() };
            console.log(copydata);
            const name = (copydata.audit_personal.label).split(" ")[0];
            const row = {'id':copydata.audit_personal.key,'name':name}
            this.props.dispatch({
              type: 'repayMent/addcopy',
              payload: {
                row:row,
                type:1
              }
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
          key:this.props.key,
          title:this.props.title,
          visible:this.props.isshowcopymodal,
          onOk: this.handleonOK,
          onCancel: this.onCancel,
        };

        const { getFieldDecorator } = this.props.form;
        const {constPersonal} = this.props.repayMent;
        const  personalOptions = constPersonal.map(data =><Option key={data.id}>{data.name}</Option>);

        return(
                <Modal {...modalOpts} >
                        <Form>
                            <FormItem {...formItemLayout} label={this.props.title} hasFeedback>
                                    {getFieldDecorator('audit_personal', {
                                        rules: [{ required: true, message: '请选择抄送人!' }]
                                    })(
                                      <Select className="t-l" labelInValue  placeholder="请选择" size="large" style={{ width: '100%' }}>
                                          {personalOptions}
                                      </Select>
                                    )}
                            </FormItem>
                        </Form>
                </Modal>
        );
    }
});

AddCopyModal.propTypes = {
    location: PropTypes.object,
    form: PropTypes.object,
    dispatch: PropTypes.func
}

function mapStateToProps({ repayMent }) {
  return { repayMent };
}

export default connect(mapStateToProps)(Form.create()(AddCopyModal));