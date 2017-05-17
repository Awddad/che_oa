import React, { PropTypes } from 'react';
import { connect } from 'dva'

import { Table, Popconfirm, Pagination, Modal, Button,Form, Row, Col, Input, Icon, Menu, Dropdown, DatePicker, Select } from 'antd';
import styles from './search.less';
import Confirm from '../details/confirmRepayment';


const MakeCollectionsList = React.createClass({
    // 筛选事件
    handleChange(pagination, filters, sorter) {

        const { type,onSorting }=this.props.payment;
        let sorting = "";
        let filterType = null;

        if (filters.type_name.length > 0) {
            filterType  = filters.type_name[0];
        }
        if (sorter.order != undefined) {
          sorting = sorter.order != 'descend' ? 1:0;
          //console.log(sorting);
        }
        this.props.onSorting(sorting, filterType);
    },
    ConfirmClick(event){
        let apply_id =event.target.getAttribute("data-applyid");
        this.props.dispatch({
            type:'Detail/RepayMentDetails',
            payload:{
                apply_id:apply_id
            }
        });

        this.props.dispatch({
            type:'Detail/RepayMentConfirmQuery',
            payload:{
                isShowRepaymentConfirm:true,
                apply_id:apply_id
            }
        });
    },
    paginationChange(page,pageNumber){
        const { perPage,keyword,begin_time,end_time }  = this.props.make_collections;
        this.props.dispatch({
            type:'make_collections/query',
            payload:{
                currentPage:page,
                perPage:perPage,
                keyword:keyword,
                begin_time:begin_time,
                end_time:end_time,
            }
        })
    },
    onShowSizeChange(current,pageSize) {
        const { perPage,keyword,begin_time,end_time }  = this.props.make_collections;
        this.props.dispatch({
            type:'make_collections/query',
            payload:{
                currentPage:current,
                perPage:pageSize,
                keyword:keyword,
                begin_time:begin_time,
                end_time:end_time,
            }
        })
    },
    render(){

        const { dataSource,keyword,begin_time,end_time,at,type,current,repayment,loading,total,sortingType} = this.props.make_collections;

        const columns = [{
            title: '序号',
            dataIndex: 'id',
            key: 'id',
            render:(text, row, index)=>(
                            index+1
                        ),
        },{
            title: '申请时间',
            dataIndex: 'create_time',
            key: 'create_time',
            sorter: (a, b) => a.create_time - b.create_time,
            filteredValue: repayment == null ? []:repayment,
        },{
            title:'类型',
            dataIndex:'type_name',
            key:'type_name',
            filters:[
                {text:'报销', value:'1'},
                {text:'借款', value:'2'},
                {text:'还款', value:'3'},
            ],
            filteredValue: repayment,
        },{
            title: '审批单编号',
            dataIndex: 'apply_id',
            key: 'apply_id'
        },{
            title:'标题',
            dataIndex:'title',
            key:'ttitle'
        },{
            title:'金额',
            dataIndex:'money',
            key:'money',
            className: 'column-money',
        },{
            title:'操作',
            dataIndex:'operation',
            render:(text,record)=> (
                <p>
                    <a className="mr-md" href={"/repaymentdetail?apply_id="+record.apply_id+"&type=confirm"}>详情</a>
                    <a data-applyid={record.apply_id} onClick={this.ConfirmClick}>收款确认</a>
                </p>
            )
        }]
        const pagination = {
            total,
            current,
            pageSize: 10,
            onChange: ()=>{},
        };

        const {RepayMent_Detail,isShowRepaymentConfirm} = this.props.Detail;
        const GenConfirm = () => <Confirm isShowRepaymentConfirm={ isShowRepaymentConfirm } details={RepayMent_Detail}/>;

        return (
            <div>
                <Button type="primary" className={styles.mt_lg}>导出列表</Button>
                <Table
                    columns={columns}
                    loading={loading}
                    dataSource={dataSource}
                    rowKey={record => record.id}
                    pagination={false}
                    size="middle"
                    bordered />
               <Pagination showQuickJumper current = { current } defaultCurrent={ 1 } total={ total } onChange={ this.paginationChange } onShowSizeChange={this.onShowSizeChange} showSizeChanger showQuickJumper/>

               <GenConfirm />

            </div>
        );
    }
})

MakeCollectionsList.propTypes = {
  onPageChange: PropTypes.func,
  dataSource: PropTypes.array,
  loading: PropTypes.any,
  total: PropTypes.any,
  current: PropTypes.any,
  Detail:PropTypes.object
};
function mapStateToProps({make_collections,Detail}){
    return { make_collections,Detail }
}
export default connect(mapStateToProps)(MakeCollectionsList);
