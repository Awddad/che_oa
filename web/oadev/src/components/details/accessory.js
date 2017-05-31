//附件显示表格
import React,{ Component,PropTypes} from 'react';
import { routerRedux } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Button,Select, Row, Col,message, Steps,Popover,Table } from 'antd';

import cs from 'classnames';
const Option = Select.Option;
const FormItem = Form.Item;

const Accessory = React.createClass({
    render(){
        const formItemLayout = {
          labelCol: {
            xs: { span: 24 },
            sm: { span: 2 },
          },
          wrapperCol: {
            xs: { span: 24 },
            sm: { span: 14 },
          },
        };

        const columns = this.props.columns;
        const dataSource = this.props.dataSource || {};

        let datasource =[];
        if(Object.keys(dataSource).length > 0){
            datasource = dataSource.file;
        }

        return(
            <div>
                <FormItem {...formItemLayout}  label="附件">
                    <Table className="zstable" size="middle" bordered columns={columns} dataSource={datasource} pagination={false} />
                </FormItem>
            </div>
        );
    }
})

Accessory.propTypes = {
   location: PropTypes.object,
   dispatch: PropTypes.func,
};

export default Accessory;