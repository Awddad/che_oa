import React, { PropTypes } from 'react';
import { connect } from 'dva'
import { Table, Popconfirm, Pagination, Modal, Button,Form, Row, Col, Input, Icon, Menu, Dropdown, DatePicker, Select } from 'antd';
import { chkPmsForBlock,chkPmsForInline,chkPmsForInlineBlock,chkPms } from '../common';

const AlreadyApproveList = React.createClass({
    // 筛选事件
    handleChange(pagination, filters, sorter) {
        let sorting = null,filterType = null,filterStatus = null;

        if (Object.keys(filters).length > 0) {
            filterType  = filters.type_value;
            filterStatus = filters.next_des;
        }
        if (sorter.order != null) {
          sorting = sorter.order != 'descend' ? 'asc':'desc';
        }
        this.props.onSorting(sorting, filterType ,filterStatus);
    },
    paginationChange(page,pageNumber){
        const { type,perPage,keywords,start_time,end_time,sort,status,at }  = this.props.alreadyApprove;
        this.props.dispatch({
            type:'alreadyApprove/query',
            payload:{
                type:type,
                page:page,
                page_size:perPage,
                keywords:keywords,
                start_time:start_time,
                end_time:end_time,
                sort:sort,
                status:status,
                at:at
            }
        })
    },
    onShowSizeChange(current,pageSize) {
        const { type,keywords,start_time,end_time,sort,status,at }  = this.props.alreadyApprove;
        this.props.dispatch({
            type:'alreadyApprove/query',
            payload:{
                type:type,
                page:current,
                page_size:pageSize,
                keywords:keywords,
                start_time:start_time,
                end_time:end_time,
                sort:sort,
                status:status,
                at:at
            }
        })
    },
    render(){

        const { dataSource,keywords,start_time,end_time,type,current,repayment,loading,total,sort,at} = this.props.alreadyApprove;
        let sortingType = null;
            if(sort == "asc"){
                sortingType = "ascend";
            }else if(sort == "desc"){
                sortingType = "descend";
            }
        const columns = [{
            title: '序号',
            dataIndex: 'id',
            key: 'id',
        },{
            title: '申请时间',
            dataIndex: 'date',
            key: 'date',
            sorter: (a, b) => a.date - b.date,
            sortOrder:sortingType,
        },{
            title: '审批单编号',
            dataIndex: 'apply_id',
            key: 'apply_id'
        },{
            title:'类型',
            dataIndex:'type_value',
            key:'type_value',
            filters:[
                {text:'报销', value:'1'},
                {text:'借款', value:'2'},
                {text:'还款', value:'3'},
            ],
            filteredValue:at,
        },{
            title:'标题',
            dataIndex:'title',
            key:'ttitle'
        },{
            title:'发起人',
            dataIndex:'person',
            key:'person'
        },{
            title:'审批人',
            dataIndex:'approval_persons',
            key:'approval_persons'

        },{
            title:'抄送人',
            dataIndex:'copy_person',
            key:'copy_person'
        },{
            title:'状态',
            dataIndex:'next_des',
            key:'next_des',
            filters:[
                {text:'审核中', value:'1'},
                {text:'财务确认中', value:'2'},
                {text:'撤销', value:'3'},
                {text:'审核不通过', value:'4'},
                {text:'完成', value:'5'},
            ],
        },{
            title:'操作',
            dataIndex:'operation',
            render:(text,record)=> {
                let result=null;
                switch(record.type_value){
                    case "报销":
                        return result = (<p><a style={chkPmsForInlineBlock(['wo_yi_shen_pi_detail'])} href={"#/reimbursedetail?apply_id="+record.apply_id}>详情</a></p>);
                        break;
                    case "借款":
                        return result = (<p><a style={chkPmsForInlineBlock(['wo_yi_shen_pi_detail'])} href={"#/loanmentdetail?apply_id="+record.apply_id}>详情</a></p>);
                        break;
                    case "还款":
                        return result = (<p><a style={chkPmsForInlineBlock(['wo_yi_shen_pi_detail'])} href={"#/repaymentdetail?apply_id="+record.apply_id}>详情</a></p>);
                        break;
                }

            }

        }]
        /*const pagination = {
            total,
            current,
            pageSize: 20,
            onChange: ()=>{},
        };*/
        return (
            <div>
              <Table
                columns={columns}
                loading={loading}
                dataSource={dataSource}
                rowKey={record => record.id}
                onChange={this.handleChange}
                pagination={false}
                size="middle"
                bordered />
            <Pagination showQuickJumper current = { current } defaultPageSize={10} defaultCurrent={ 1 } total={ total } onChange={ this.paginationChange } onShowSizeChange={this.onShowSizeChange} showSizeChanger showQuickJumper/>
            </div>
        );
    }
})

AlreadyApproveList.propTypes = {
  onPageChange: PropTypes.func,
  dataSource: PropTypes.array,
  loading: PropTypes.any,
  total: PropTypes.any,
  current: PropTypes.any,
};
function mapStateToProps({alreadyApprove}){
    return { alreadyApprove }
}
export default connect(mapStateToProps)(AlreadyApproveList);
