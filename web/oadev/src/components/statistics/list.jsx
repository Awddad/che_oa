import React, { PropTypes } from 'react';
import { connect } from 'dva'
import { Table, Popconfirm, Pagination, Modal, Button,Form, Row, Col, Input, Icon, Menu, Dropdown, DatePicker, Select } from 'antd';
import { routerRedux,Link } from 'dva/router';
import styles from './search.less';
import { chkPmsForBlock,chkPmsForInline,chkPmsForInlineBlock,chkPms } from '../common';

const LoadDetailsList= React.createClass({
    // 筛选事件
    handleChange(pagination, filters, sorter) {
        let sorting = null;
        if (sorter.order != null) {
          sorting = sorter.order != 'descend' ? 'asc':'desc';
        }
        this.props.onSorting(sorting);
    },
    paginationChange(page,pageNumber){
        const { perPage,key,time }  = this.props.Statistics;
        this.props.dispatch({
            type:'Statistics/query',
            payload:{
                page:page,
                pageSize:perPage,
                key:key,
                time:time,
            }
        })
    },
    onShowSizeChange(current,pageSize){
        //console.log(pageSize);
        const { key,time }  = this.props.Statistics;
        this.props.dispatch({
            type:'Statistics/query',
            payload:{
                page:current,
                pageSize:pageSize,
                key:key,
                time:time,
            }
        })
    },

    render(){

        const { dataSource,keyword,time,type,current,totalCount,pageSize,pageCount,perPage,currentPage,loading,total,sort} = this.props.Statistics;
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
                    render:(text, row, index)=>(
                                    index+1
                                ),
        },{
            title: '借款时间',
            dataIndex: 'get_money_time',
            key: 'get_money_time',
            sorter: (a, b) => a.get_money_time - b.get_money_time,
            sortOrder:sortingType,
        },{
            title: '借款人',
            dataIndex: 'person',
            key: 'person'
        },{
            title:'部门',
            dataIndex:'org',
            key:'org'
        },{
            title:'借款金额',
            dataIndex:'money',
            key:'money',
            className: 'column-money',
        },{
            title:'事由',
            dataIndex:'des',
            key:'des'
        },{
            title:'操作',
            dataIndex:'operation',
            render:(text,record)=>(
                <p>
                    <Link style={chkPmsForInlineBlock(['yuan_gong_jie_kuan_ming_xi_biao_detail'])} to={"/loanmentdetail?apply_id="+record.apply_id}>详情</Link>
                </p>
            )
        }]
        /*const pagination = {
            total,
            current,
            pageSize: 20,
            onChange: ()=>{},
        };*/

        return (
            <div>
                <Button type="primary" className={styles.mt_lg}>导出列表</Button>
                    <Table
                        columns={columns}
                        loading={loading}
                        dataSource={dataSource}
                        rowKey={record => record.id}
                        onChange = {this.handleChange}
                        pagination={false}
                        size="middle"
                        bordered

                    />
                    <Pagination
                        showQuickJumper
                        current = { current }
                        defaultCurrent={ 1 }
                        defaultPageSize={10}
                        total={ total }
                        onChange={ this.paginationChange }
                        onShowSizeChange={this.onShowSizeChange}
                        showSizeChanger
                        showQuickJumper
                    />
            </div>
        );
    }
})
LoadDetailsList.propTypes = {
  onPageChange: PropTypes.func,
  dataSource: PropTypes.array,
  loading: PropTypes.any,
  total: PropTypes.any,
  current: PropTypes.any,
};
function mapStateToProps({Statistics}){
    return { Statistics }
}
export default connect(mapStateToProps)(LoadDetailsList);
