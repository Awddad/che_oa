import React, { PropTypes } from 'react';
import { connect } from 'dva'
import { routerRedux,Link } from 'dva/router';
import { Table, Popconfirm, Pagination, Modal, Button,Form, Row, Col, Input, Icon, Menu, Dropdown, DatePicker, Select } from 'antd';


const CcsendList = React.createClass({
    // 筛选事件
    handleChange(pagination, filters, sorter) {

        //console.log(filters);
        //console.log(sorter);
        const { at,type,ob,status,onSorting }=this.props.ccsend;
        let sorting = "";
        let filterType = null;
        let filterStatus = null;

        if (filters.type_value.length > 0) {
            filterType  = filters.type_value[0];
        }

        if (filters.next_des.length > 0) {
            filterStatus  = filters.next_des[0];
        }
        if (sorter.order != undefined) {
          sorting = sorter.order != 'descend' ? 1:0;
          //console.log(sorting);
        }
        this.props.onSorting(sorting, filterType, filterStatus);
    },
    paginationChange(page,pageNumber){
        const { type,perPage,keywords,start_time,end_time,ob,status,at }  = this.props.ccsend;
        this.props.dispatch({
            type:'ccsend/query',
            payload:{
                type:type,
                page:page,
                page_size:perPage,
                keywords:keywords,
                start_time:start_time,
                end_time:end_time,
                ob:ob,
                status:status,
                at:at
            }
        })
    },
    onShowSizeChange(current,pageSize){
        const {type,perPage,keywords,start_time,end_time,ob,status,at }  = this.props.ccsend;
        this.props.dispatch({
            type:'ccsend/query',
            payload:{
                type:type,
                page:current,
                page_size:pageSize,
                keywords:keywords,
                start_time:start_time,
                end_time:end_time,
                ob:ob,
                status:status,
                at:at
            }
        })
    },
    render(){

        const { dataSource,keywords,start_time,end_time,at,type,current,pageSize,pageCount,perPage,currentPage,repayment,loading,total,sortingType,showDetail} = this.props.ccsend;

        //console.log(dataSource);
            const columns = [{
                title: '序号',
                dataIndex: 'id',
                key: 'id',
                render:(text, row, index)=>(
                                index+1
                            ),
            },{
                title: '申请时间',
                dataIndex: 'date',
                key: 'date',
                render:(text, record, index)=>{
                    return record.date;
                },
                sorter: (a, b) => a.date - b.date,
                sortOrder:sortingType == "date" ? sorting : "",
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
                filteredValue: repayment == null ? []:repayment,
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
                    {text:'审核不通过 ', value:'4'},
                    {text:'完成', value:'5'},
                ],
                filteredValue: repayment == null ? []:repayment,
            },{
                title:'操作',
                dataIndex:'operation',
                render:(text,record)=> {
                    //console.log(record);
                    let result=null;
                    switch(record.type_value){
                        case "报销":
                            return result = (<p><Link to={"/reimbursedetail?apply_id="+record.apply_id}>详情</Link></p>);
                            break;
                        case "借款":
                            return result = (<p><Link to={"/loanmentdetail?apply_id="+record.apply_id}>详情</Link></p>);
                            break;
                        case "还款":
                            return result = (<p><Link to={"/repaymentdetail?apply_id="+record.apply_id}>详情</Link></p>);
                            break;
                    }

                }
            }];

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
                        bordered
                    />
                    <Pagination
                        showQuickJumper
                        current = { current }
                        defaultCurrent={ 1 }
                        total={ total }
                        defaultPageSize={10}
                        onChange={ this.paginationChange }
                        onShowSizeChange={this.onShowSizeChange}
                        showSizeChanger
                        showQuickJumper
                    />
                </div>
            );
        }
    });

CcsendList.propTypes = {
  onPageChange: PropTypes.func,
  dataSource: PropTypes.array,
  loading: PropTypes.any,
  total: PropTypes.any,
  current: PropTypes.any,
};

function mapStateToProps({ccsend}){
    return { ccsend }
}
export default connect(mapStateToProps)(CcsendList);
