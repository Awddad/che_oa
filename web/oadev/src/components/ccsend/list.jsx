import React, { PropTypes } from 'react';
import { connect } from 'dva'
import { routerRedux,Link } from 'dva/router';
import { Table, Popconfirm, Pagination, Modal, Button,Form, Row, Col, Input, Icon, Menu, Dropdown, DatePicker, Select } from 'antd';
import { chkPmsForBlock,chkPmsForInlineBlock } from '../common';

const CcsendList = React.createClass({
    // 筛选事件
    handleChange(pagination, filters, sorter) {
        let sorting = "";
        let filterType = null;

        if (filters.type_value.length > 0) {
            filterType  = filters.type_value;
        }else{
            filterType  = [];
        }

        if (sorter.order != undefined) {
          sorting = sorter.order != 'descend' ? 'desc':'asc';
        }
        this.props.onSorting(sorting, filterType);
    },
    paginationChange(page,pageNumber){
        const { type,perPage,keywords,start_time,end_time,sort,at }  = this.props.ccsend;
        this.props.dispatch({
            type:'ccsend/query',
            payload:{
                type:type,
                page:page,
                page_size:perPage,
                keywords:keywords,
                start_time:start_time,
                end_time:end_time,
                sort:sort,
                at:at
            }
        })
    },
    onShowSizeChange(current,pageSize){
        const {type,perPage,keywords,start_time,end_time,sort,at }  = this.props.ccsend;
        this.props.dispatch({
            type:'ccsend/query',
            payload:{
                type:type,
                page:current,
                page_size:pageSize,
                keywords:keywords,
                start_time:start_time,
                end_time:end_time,
                sort:sort,
                at:at
            }
        })
    },
    render(){

        const { dataSource,keywords,start_time,end_time,at,sort,type,current,pageSize,pageCount,perPage,currentPage,loading,total,showDetail} = this.props.ccsend;
        let sortingType = null;
            if(sort == "asc"){
                sortingType = "ascend";
            }else if(sort == "desc"){
                sortingType = "descend";
            }else{
                sortingType = false;
            }
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
            },{
                title:'操作',
                dataIndex:'operation',
                render:(text,record)=> {
                    let result=null;
                    switch(record.type_value){
                        case "报销":
                            return result = (<p style={chkPmsForInlineBlock(["chao_song_gei_wo_detal"])} ><Link to={"/reimbursedetail?apply_id="+record.apply_id}>详情</Link></p>);
                            break;
                        case "借款":
                            return result = (<p style={chkPmsForInlineBlock(["chao_song_gei_wo_detal"])}><Link to={"/loanmentdetail?apply_id="+record.apply_id}>详情</Link></p>);
                            break;
                        case "还款":
                            return result = (<p style={chkPmsForInlineBlock(["chao_song_gei_wo_detal"])}><Link to={"/repaymentdetail?apply_id="+record.apply_id}>详情</Link></p>);
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
