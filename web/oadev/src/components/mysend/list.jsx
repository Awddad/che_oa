import React, { PropTypes } from 'react';
import { connect } from 'dva'
import { routerRedux } from 'dva/router';
import styles from './search.less';
import { Table, Popconfirm, Pagination, Modal, Button,Form, Row, Col, Input, Icon, Menu, Dropdown, DatePicker, Select } from 'antd';

const MysendList = React.createClass({
    // 筛选事件
    handleChange(pagination, filters, sorter) {
        const { at,type,onSorting }=this.props.mysend;
        let sorting = "";
        let filterType = null;

        if (filters.type_value.length > 0) {
            filterType  = filters.type_value[0];
        }
        if (sorter.order != undefined) {
          sorting = sorter.order != 'descend' ? 1:0;
          //console.log(sorting);
        }
        this.props.onSorting(sorting, filterType);
    },
    paginationChange(page,pageNumber){
        const { type,perPage,keywords,start_time,end_time,ob,status,at }  = this.props.mysend;
        this.props.dispatch({
            type:'mysend/query',
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
    onShowSizeChange(current,pageSize) {
        const { type,keywords,start_time,end_time,ob,status,at }  = this.props.mysend;
        this.props.dispatch({
            type:'mysend/query',
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
        const { dataSource,keywords,start_time,end_time,at,type,current,pageSize,pageCount,perPage,currentPage,repayment,loading,total,sortingType,onPageChange,onDeleteItem,onShowSizeChange} = this.props.mysend;
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
            },{
                title:'操作',
                dataIndex:'operation',
                render:(text,record)=>{
                    let result=null,url=null;
                    switch(record.type_value){
                        case "报销":
                            url = "#/reimbursedetail?apply_id="+record.apply_id;
                            break;
                        case "借款":
                            url = "#/loanmetdetail?apply_id="+record.apply_id;
                            break;
                        case "还款":
                            url = "#/repaymentdetail?apply_id="+record.apply_id;
                            break;
                    }


                    return result = (<p><a className="mr-md" href={url}>详情</a>
                                <span className={record.can_cancel==1?styles.show:styles.hide} data-applyid={record.apply_id} onClick={this.props.handleClick}>
                                    <a>撤销</a>
                                </span>
                            </p>);

                }
            }]

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

MysendList.propTypes = {
    location:PropTypes.object,
    dispatch: PropTypes.func,
    UserInfo:PropTypes.object,
    onPageChange: PropTypes.func,
    onDeleteItem: PropTypes.func,
    dataSource: PropTypes.array,
    loading: PropTypes.any,
    total: PropTypes.any,
    current: PropTypes.any,
};
function mapStateToProps({mysend,UserInfo}){
    return { mysend,UserInfo }
}
export default connect(mapStateToProps)(MysendList);

