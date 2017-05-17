import React, { PropTypes } from 'react';
import { connect } from 'dva'
import { routerRedux,Link } from 'dva/router';
import { Table, Popconfirm, Pagination, Modal, Button,Form, Row, Col, Input, Icon, Menu, Dropdown, DatePicker, Select } from 'antd';
import styles from './search.less';
import Confirm from '../details/confirmPayment';


const PaymentdList= React.createClass({
    getInitialState(){
        return {
           status:1
        };
    },
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
    bxConfirmClick(event){
        this.setState({
            status:1
        });
        let apply_id =event.target.getAttribute("data-applyid");
        this.props.dispatch({
            type:'Detail/BaoxiaoDetails',
            payload:{
                apply_id:apply_id
            }
        });

        this.props.dispatch({
            type:'Detail/PayMentConfirmQuery',
            payload:{
                isShowPaymentConfirm:true,
                apply_id:apply_id
            }
        });
    },

    loanConfirmClick(event){
        this.setState({
            status:2
        });
        let apply_id =event.target.getAttribute("data-applyid");
        this.props.dispatch({
            type:'Detail/LoanDetails',
            payload:{
                apply_id:apply_id
            }
        });

        this.props.dispatch({
            type:'Detail/PayMentConfirmQuery',
            payload:{
                isShowPaymentConfirm:true,
                apply_id:apply_id
            }
        });
    },
   paginationChange(page,pageNumber){
        const { perPage,keyword,begin_time,end_time }  = this.props.payment;
        console.log(perPage);
        this.props.dispatch({
            type:'payment/query',
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
        const { perPage,keyword,begin_time,end_time }  = this.props.payment;
        this.props.dispatch({
            type:'payment/query',
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

        const { dataSource,keyword,begin_time,end_time,at,type,current,repayment,loading,total,sortingType,onPageChange} = this.props.payment;

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
            sortOrder:sortingType == "date" ? sorting : "",
        },{
            title:'类型',
            dataIndex:'type_name',
            key:'type_name',
            filters:[
                {text:'报销', value:'1'},
                {text:'借款', value:'2'},
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
            render:(text,record)=>
            {
                let result=null,url=null,confirmclick=null;
                switch(record.type_name){
                    case '申请报销':
                        url = "/reimbursedetail?apply_id="+record.apply_id+"&type=confirm";
                        confirmclick = this.bxConfirmClick;
                    break;
                    case '申请借款':
                        url = "/loanmentdetail?apply_id="+record.apply_id+"&type=confirm";
                        confirmclick = this.loanConfirmClick;
                    break;
                }

                return (
                            <p>
                                <Link className="mr-md" to={url}>详情</Link>
                                <a data-applyid={record.apply_id} onClick={confirmclick}>付款确认</a>
                            </p>
                )

            }

        }]
        const pagination = {
            total,
            current,
            pageSize: 20,
            onChange: ()=>{},
        };

        const {Baoxiao_Detail,LoanMent_Detail,isShowPaymentConfirm} = this.props.Detail;
        let details = this.state.status == 1 ? Baoxiao_Detail : LoanMent_Detail;
        const GenConfirm = () => <Confirm isShowPaymentConfirm={ isShowPaymentConfirm } details={details}/>;


        return (
            <div>
                <GenConfirm  />
                <Button type="primary" className={styles.mt_lg}>导出列表</Button>
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

PaymentdList.propTypes = {
  onPageChange: PropTypes.func,
  dataSource: PropTypes.array,
  loading: PropTypes.any,
  total: PropTypes.any,
  current: PropTypes.any,
  Detail:PropTypes.object
};
function mapStateToProps({payment,Detail}){
    return { payment,Detail }
}
export default connect(mapStateToProps)(PaymentdList);
