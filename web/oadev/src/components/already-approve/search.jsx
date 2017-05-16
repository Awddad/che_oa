import React, { PropTypes } from 'react';
import { Form, Row, Col, Input, Button, Icon, Menu, Dropdown, DatePicker, Select } from 'antd';
import moment from 'moment';
import styles from './search.less';

const FormItem = Form.Item;

const AlreadyApproveSearch = ({
    keywords,
    department,
    start_time,
    end_time,
    handleSearch,
    handleReset,
    form: {
        getFieldDecorator,
        validateFields,
        getFieldsValue,
        resetFields,
    },
    }) => {
    const formItemLayout = {
      labelCol: { span: 6 },
      wrapperCol: { span: 18 },
    };
    // 清除检索项
    handleReset = () => {
        resetFields();
    }

    function handleSubmit(e){
        e.preventDefault();
        validateFields((errors) => {
            if(!!errors){
                return;
            }
            handleSearch(getFieldsValue());
        })
    }

    const { MonthPicker, RangePicker } = DatePicker;
    const dateFormat = 'YYYY-MM-DD';
    const rangeConfig = {
      rules: [{ type: 'array', required: false, message: '请选择时间!' }],
    };

    const children = [];
    children.push(
        <Col span={8} key={1}>
            <FormItem {...formItemLayout} label={`关键字`}>
                {getFieldDecorator('keywords', {
                    initialValue: keywords,
                })(
                <Input placeholder="编号/标题/发起人/审批人/抄送人" />
                )}
            </FormItem>
        </Col>
    );
    children.push(
        <Col span={8} key={3}>
            <FormItem {...formItemLayout} label={`借款时间`}>
                {getFieldDecorator('begin_end_time',rangeConfig)(
                    <RangePicker format={dateFormat}/>
                )}
            </FormItem>
        </Col>
    );
      
    return (
        <div className={styles.normal}>
            <Form
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
export default Form.create()(AlreadyApproveSearch);
