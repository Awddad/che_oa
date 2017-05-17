import React,{ Component,PropTypes} from 'react';
import { connect } from 'dva';
import cs from 'classnames'
import { Checkbox,Table,Row,Col,Form,Popconfirm } from 'antd';

class ApplyTable extends React.Component{
    state ={
      isshowfoot:false
    }
    onDelete = (index) => {
      const tabledata = [...this.props.tabledata];
      tabledata.splice(index, 1);
      this.props.dispatch({
          type: 'reimBurse/table_del_modelHandle',
          payload: tabledata,
      });
    }
    render() {
        const columns = [{
          title: '序号',
          dataIndex: 'name',
          width:60,
          key:'name',
          render: (text, row, index) => index + 1}
        , {
          title: '报销金额',
          key:'money',
          dataIndex: 'money',
          width:120,
          className:cs("t-r"),
        }, {
          title: '报销类别',
          key:'type_name',
          dataIndex: 'type_name',
          className:cs("t-c"),
        },{
          title: '费用明细',
          key:'des',
          dataIndex: 'des',
        },
        {
          title: '操作',
          key:'option',
          dataIndex: 'option',
          render: (text, record, index) => {
            return (
              this.props.tabledata.length > 1 ?
              (
                <Popconfirm title="你确定要删除吗?" onConfirm={() => this.onDelete(index)}>
                  <a href="javascript:;">删除</a>
                </Popconfirm>
              ) : null
            );
          }
        }];

        const rowSelection = {
          onChange: (selectedRowKeys, selectedRows) => {
            console.log(`selectedRowKeys: ${selectedRowKeys}`, 'selectedRows: ', selectedRows);
          },
          getCheckboxProps: record => ({
            disabled: record.name === 'Disabled User',
          }),
        };

        const tabledata = this.props.tabledata || [];
        let count = 0;
        if(tabledata.length > 0){
          for(let i=0; i<tabledata.length;i++){
              count = count + tabledata[i].money *1;
          }
        }

        return (
          <div>
            <Table className={cs("ant-col-sm-24")} size="middle" columns={columns} dataSource={this.props.tabledata} pagination={false} rowKey={record => record.index} footer={() => (<table><tbody><tr><td width="60">合计</td><td width="104" className="t-r">{count.toFixed(2)}</td><td colSpan="3"></td></tr></tbody></table>)} />

          </div>
        );
    }
};

ApplyTable.propTypes = {
    location: PropTypes.object,
    dispatch: PropTypes.func
}

export default connect()(ApplyTable);