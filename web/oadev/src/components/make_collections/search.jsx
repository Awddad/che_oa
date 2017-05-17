import React, { PropTypes } from 'react';
import { Form, Row, Col, Input, Button, Icon, Menu, Dropdown, DatePicker, Select } from 'antd';
import moment from 'moment';
import styles from './search.less';

const FormItem = Form.Item;

const MakeCollectionSearch = ({
    keyword,
    department,
    handleSearch,
    handleReset,
    form: {
        getFieldDecorator,
        validateFields,
        getFieldsValue,
        resetFields
    },
    }) => {

    const formItemLayout = {
        labelCol: {
            xs: { span: 24 },
            sm: { span: 6 },
        },
        wrapperCol: {
            xs: { span: 24 },
            sm: { span: 18 },
        },
    };

    const ColSpan = {
        xs: 24,
        sm: 8,
    }
    
    function handleSubmit(e) {
        e.preventDefault();
        validateFields((errors) => {
          if (!!errors) {
            return;
          }
          handleSearch(getFieldsValue());
        });
      }

    const { MonthPicker, RangePicker } = DatePicker;
    const dateFormat = 'YYYY/MM/DD';
    const rangeConfig = {
      rules: [{ type: 'array', required: false, message: '请选择时间!' }],
    };
    
    
    const children = [];
      children.push(
        <Col {...ColSpan} key={1}>
          <FormItem {...formItemLayout} label={`关键字`}>
              {getFieldDecorator('keyword', {
                  initialValue: keyword,
                })(
              <Input placeholder="编号/标题/" />
                )}
          </FormItem>
        </Col>
      );
      children.push(
        <Col {...ColSpan} key={3}>
          <FormItem {...formItemLayout} label={`申请时间`}>
                {getFieldDecorator('begin_end_time',rangeConfig)(
                    <RangePicker format={dateFormat}/>
                )}
         </FormItem>
        </Col>
      );
      
    return (
        <div className={styles.normal}>
            <Form
                horizontal
                className="ant-advanced-search-form"
                onSubmit={handleSubmit}
              >
                <Row gutter={40}>
                    {children.slice(0, 3)}
                </Row>
                <Row>
                    <Col span={24} style={{ textAlign: 'right' }}>
                        <Button type="primary" htmlType="submit">搜索</Button>
                        <Button style={{ marginLeft: 8 }} onClick={handleReset}>清除</Button>
                    </Col>
                </Row>
            </Form>
        </div>
    );
}


export default Form.create()(MakeCollectionSearch);
