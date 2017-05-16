import { Table, Popconfirm, Pagination, Modal, Button,Form, Row, Col, Input, Icon, Menu, Dropdown, DatePicker, Select, Popover } from 'antd';
import React, { PropTypes } from 'react';
import numeral from 'numeral';

const FormItem = Form.Item;
const dateFormat = 'YYYY/MM/DD';

const PayModal = ({
    dispatch,
    loan = {},
    visible,
    handlePay,
    handleCancel,
    form: {
        getFieldDecorator,
        validateFields,
        getFieldsValue
    }
}) => {

 const formItemLayout = {
  labelCol: { span: 6 },
  wrapperCol: { span: 14 },
};

  function handleSubmit(){
    validateFields((errors) => {
        if (errors) {
          return;
        }
        const data = {...getFieldsValue()};
        handlePay(data);
    });
  }
  const text = <span>Title</span>;
const modalOpts = {
  title: '付款确认',
  visible,
  maskClosable: false,
  onOk: handleSubmit,
  onCancel:handleCancel
};

    return (
        <Modal {...modalOpts}>
        <Form horizontal>

           <FormItem label="付款部门:" {...formItemLayout}>

           </FormItem>
            <FormItem {...formItemLayout} label='收款类型:'>
            {getFieldDecorator('interestDay', {
                rules: [{ type:'object', required: true, message: '请选择计息日期'}]
              })(
                  <Select placeholder="请选择国家" style={{ width: '100%' }}>
                    <Option value="china">中国</Option>
                    <Option value="use">美国</Option>
                    <Option value="japan">日本</Option>
                    <Option value="korean">韩国</Option>
                    <Option value="Thailand">泰国</Option>
                  </Select>
              )}
          </FormItem>

          <FormItem {...formItemLayout} label='收款类型:'>
            {getFieldDecorator('orderNo', {
                rules: [
                        { max:50 , message: '长度不能超过50个字符'}
                    ],
              })(
                  <Select placeholder="请选择国家" style={{ width: '100%' }}>
                    <Option value="china">中国</Option>
                    <Option value="use">美国</Option>
                    <Option value="japan">日本</Option>
                    <Option value="korean">韩国</Option>
                    <Option value="Thailand">泰国</Option>
                  </Select>
              )}
          </FormItem>

          <FormItem {...formItemLayout} label='流水号:'>
            {getFieldDecorator('comment', {
                rules: [
                        { max:200 , message: '长度不能超过200个字符'}
                    ],
              })(
                  <Input maxLength="200" placeholder="" size="default" />
              )}
          </FormItem>
          <FormItem
              labelCol={{ span: 10 }}
              wrapperCol={{ span: 14 }}  
              label= '付款时间'
              className={styles.form_label}
              required={true}
            >
              {getFieldDecorator(`recoverDateList${i}`, {
                validateTrigger: ['onChange', 'onBlur'],
                valuePropName: 'defaultValue',
                initialValue: isInitRecoverDate==true?moment(recoverDateList[i], 'YYYY-MM-DD'):moment(new Date(), 'YYYY-MM-DD'),
                rules: [{ required: true, message: `请选择付款时间-${i+1}`,type:'object' }],
              })(
                <DatePicker style={{width: 300,fontSize:14,height:32 }} format='YYYY-MM-DD'/>
              )}
         </FormItem>
          <FormItem {...formItemLayout} label='付款给:'>
                <p></p>
          </FormItem>
          <FormItem {...formItemLayout} label='备注:'>
            {getFieldDecorator('comment', {
                rules: [
                        { max:200 , message: '长度不能超过200个字符'}
                    ],
              })(
                  <Input maxLength="200" placeholder="" size="default" />
              )}
          </FormItem>
        </Form>
      </Modal>
    );
}

export default Form.create()(LoanPayModal);
