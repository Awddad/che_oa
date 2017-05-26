//报销明细表格
import React,{ Component,PropTypes} from 'react';
import { routerRedux } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Button,Select, Row, Col,message, Steps,Popover,Table } from 'antd';
import styles from '../../routes/style.less';
import { host } from '../common';
import cs from 'classnames';
const Option = Select.Option;
const FormItem = Form.Item;

const BxDetail = React.createClass({
    render(){
        const columns = this.props.columns;
        const dataSource = this.props.dataSource || {};
        const label = this.props.label;
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
        let count = 0,datasource =[];
        if(Object.keys(dataSource).length > 0){
            datasource = dataSource.list;
            for(let i=0;i<datasource.length;i++){
                count = count + datasource[i].money*1;
            }
        }
        return(
            <div>
                <h2 className={cs('mt-md','mb-md')}><strong>需审批内容</strong><a className={cs(styles.download,'ml-sm')} href={this.props.pdf !=null ? host+this.props.pdf:"javascript:;"}>下载审批单</a></h2>
                <FormItem {...formItemLayout}  label={label}>
                    <Table className="zstable" columns={columns} dataSource={datasource} pagination={false} size="middle" bordered footer={() => (<table><tbody><tr><td width="60">合计</td><td width="104" className="t-r">{count.toFixed(2)}</td><td colSpan="3"></td></tr></tbody></table>)} />
                </FormItem>
            </div>
        );
    }
})

BxDetail.propTypes = {
   location: PropTypes.object,
   dispatch: PropTypes.func,
};

export default BxDetail;