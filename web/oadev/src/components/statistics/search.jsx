import React, { PropTypes } from 'react';
import { connect } from 'dva';
import { Form, Row, Col, Input, Button, Icon, Menu, Dropdown, DatePicker, Select , Cascader } from 'antd';
import moment from 'moment';
import styles from './search.less';

const FormItem = Form.Item;

const LoadDetailSearch = React.createClass({
    // 清除检索项
    handleReset (){
        this.props.form.resetFields();
    },
    handleSubmit(e){
        const {
            getFieldDecorator,
            validateFields,
            getFieldsValue,
            getFieldProps,
            handleSearch,
        } = this.props.form;
        e.preventDefault();
        const detail = {...getFieldsValue()};
        //console.log(detail);
        validateFields((errors) => {
            if(!!errors){
                return;
            }
            this.props.handleSearch(detail);
        })
    },
    render(){
        const { dataSource,key,start_time,end_time,current,currentPage,loading,total,onPageChange} = this.props.Statistics;

        const {
            getFieldDecorator,
            validateFields,
            getFieldsValue,
            getFieldProps,
        } = this.props.form;

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

        
        const { MonthPicker, RangePicker } = DatePicker;
        const dateFormat = 'YYYY-MM-DD';
        const rangeConfig = {
          rules: [{ type: 'array', required: false, message: '请选择时间!' }],
        };

        const children = [];
        children.push(
            <Col {...ColSpan} key={1}>
                <FormItem {...formItemLayout} label={`关键字`}>
                    {getFieldDecorator('key', {
                        initialValue: key,
                    })(
                    <Input placeholder="借款人/事由" />
                    )}
                </FormItem>
            </Col>
        );
        const options = this.props.Statistics.department;
        children.push(
            <Col {...ColSpan} key={2}>
                <FormItem {...formItemLayout} label={`部门`}>
                    {getFieldDecorator('department')(
                        <Cascader options={options} placeholder="请选择" />
                    )}
                </FormItem>
            </Col>
        );
        children.push(
            <Col {...ColSpan} key={3}>
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
                onSubmit={this.handleSubmit}
                >
                    <Row gutter={40}>
                        {children.slice(0, 3)}
                    </Row>
                    <Row>
                        <Col span={24} style={{ textAlign: 'right' }}>
                            <Button type="primary" htmlType="submit">搜索</Button>
                            <Button style={{ marginLeft: 8 }} onClick={this.handleReset}>清除</Button>
                        </Col>
                    </Row>
                </Form>
            </div>
        );
    }
})

LoadDetailSearch.propTypes = {
    location:PropTypes.object,
    dispatch: PropTypes.func,
    UserInfo:PropTypes.object,
    onPageChange: PropTypes.func,
    dataSource: PropTypes.array,
    loading: PropTypes.any,
    Statistics:PropTypes.object,
};

function mapStateToProps({Statistics}){
    return { Statistics }
}

export default connect(mapStateToProps)(Form.create()(LoadDetailSearch));
