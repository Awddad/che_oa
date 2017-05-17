import React, { PropTypes } from 'react';
import { Form, Input, Modal,Checkbox,Radio,Button,Switch,message } from 'antd';
const FormItem = Form.Item;
const RadioButton = Radio.Button;
const RadioGroup = Radio.Group;
const CheckboxGroup = Checkbox.Group;

const formItemLayout = {
  labelCol: {
    span: 6,
  },
  wrapperCol: {
    span: 14,
  },
};

const paymentModal = ({
  visible,
  item = {},
  type = "create",
  validMobile,
  validName,
  onOk,
  onCancel,
  form: {
    getFieldDecorator,
    validateFields,
    getFieldsValue,
    getFieldValue,
    },
  }) => {
  function handleOk() {
    validateFields((errors) => {
      if (errors) {
        return;
      }
      if(getFieldValue('name')==getFieldValue('agent')){
         message.error("企业名称与联系人名称不能相同!");
         return;
      }
      const data = { ...getFieldsValue()};
      onOk(data);
    });
  }


  return (
    <Modal {...modalOpts}>
        <Form horizontal>
            <FormItem
              {...formItemLayout}
              label="付款部门"
              hasFeedback
            >
              {getFieldDecorator('付款部门', {
                rules: [
                  { required: true, message: 'Please select your country!' },
                ],
              })(
                <Select placeholder="请选择">
                  <Option value="china">11</Option>
                  <Option value="use">11</Option>
                </Select>
              )}
            </FormItem>

            <FormItem
              {...formItemLayout}
              label="付款账号"
              hasFeedback
            >
              {getFieldDecorator('付款账号', {
                rules: [
                  { required: true, message: 'Please select your country!' },
                ],
              })(
                <Select placeholder="请选择">
                  <Option value="china">11</Option>
                  <Option value="use">11</Option>
                </Select>
              )}
            </FormItem>

            <FormItem
              {...formItemLayout}
              label="收款类型"
              hasFeedback
            >
              {getFieldDecorator('收款类型', {
                rules: [
                  { required: true, message: 'Please select your country!' },
                ],
              })(
                <Select placeholder="请选择">
                  <Option value="china">11</Option>
                  <Option value="use">11</Option>
                </Select>
              )}
            </FormItem>
            <FormItem
                label="流水号"
                {...formItemLayout}
              >
                <Input placeholder="请输入" />
            </FormItem>

            <FormItem validateStatus="error" help="Please select the correct date">
              <DatePicker />
            </FormItem>      

        </Form>
    </Modal>
  );
};

paymentModal.propTypes = {
  visible: PropTypes.any,
  type:PropTypes.any,
  form: PropTypes.object,
  item: PropTypes.object,
  onOk: PropTypes.func,
  onCancel: PropTypes.func,
  validMobile: PropTypes.func, 
};

export default Form.create()(paymentModal);
