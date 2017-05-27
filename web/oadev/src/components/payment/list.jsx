import React, { PropTypes } from 'react';
import { connect } from 'dva'
import { routerRedux,Link } from 'dva/router';
import { Table, Popconfirm, Pagination, Modal, Button,Form, Row, Col, Input, Icon, Menu, Dropdown, DatePicker, Select } from 'antd';
import styles from './search.less';
import Confirm from '../details/confirmPayment';
import { chkPmsForBlock,chkPmsForInline,chkPmsForInlineBlock,chkPms,host } from '../common';


const PaymentdList= React.createClass({
    getInitialState(){
        return {
           status:1
        };
    },
    // 筛选事件
    handleChange(pagination, filters, sorter) {
        let sorting = null;
        let filterType = null;
        if (Object.keys(filters).length > 0) {
            filterType  = filters.type_name[0];
        }else{
            filterType = '';
        }
        if (sorter.order != null) {
          sorting = sorter.order != 'descend' ? 'asc':'desc';
        }
        this.props.onSorting(sorting, filterType);
    },
    bxConfirmClick(event){
        this.setState({
            status:1
        });
        let apply_id =event.target.getAttribute("data-applyid");
        this.props.dispatch({
            type:'Detail/PayMentConfirmQuery',
            payload:{
                isShowPaymentConfirm:true,
                apply_id:apply_id,
                type:'bx'
            }
        });
    },
    loanConfirmClick(event){
        this.setState({
            status:2
        });
        let apply_id =event.target.getAttribute("data-applyid");
        this.props.dispatch({
            type:'Detail/PayMentConfirmQuery',
            payload:{
                isShowPaymentConfirm:true,
                apply_id:apply_id,
                type:'loan'
            }
        });

    },
   paginationChange(page,pageNumber){
        const { perPage,keyword,begin_time,end_time,sort,type}  = this.props.payment;
        this.props.dispatch({
            type:'payment/query',
            payload:{
                type:type,
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
    handleClick(){
        const { keyword,begin_time,end_time,type }  = this.props.payment;
        let ttype="";
        let endtime = end_time == null?'':end_time;
        ttype = type == undefined ? "" : type;
        window.location.href = host + "/oa_v1/pay-confirm/export?keyword="+keyword+"&begin_time="+begin_time+"&end_time="+endtime+"&type="+ttype ;
    },
    render(){

        const { dataSource,keyword,begin_time,end_time,at,sort,type,current,repayment,loading,total,onPageChange} = this.props.payment;
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
            title: '申请时间',
            dataIndex: 'create_time',
            key: 'create_time',
            sorter: (a, b) => a.create_time - b.create_time,
            sortOrder:sortingType,
        },{
            title:'类型',
            dataIndex:'type_name',
            key:'type_name',
            filters:[
                {text:'报销', value:'1'},
                {text:'借款', value:'2'},
            ],
            filteredValue: at,
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
                        url = "/reimbursedetail?type=confirm&apply_id="+record.apply_id;
                        confirmclick = this.bxConfirmClick;
                    break;
                    case '申请借款':
                        url = "/loanmentdetail?type=confirm&apply_id="+record.apply_id;
                        confirmclick = this.loanConfirmClick;
                    break;
                }

                return (
                            <p>
                                <Link className="mr-md" to={url} style={chkPmsForInlineBlock(['fu_kuan_que_ren_detail'])}>详情</Link>
                                <a data-applyid={record.apply_id}  style={chkPmsForInlineBlock(['fu_kuan_que_ren'])} onClick={confirmclick}>付款确认</a>
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

        const {Baoxiao_Detail,Loan_Detail,isShowPaymentConfirm} = this.props.Detail;
        let details = this.state.status == 1 ? Baoxiao_Detail : Loan_Detail;

        return (
            <div>
                <Confirm isShowPaymentConfirm={ isShowPaymentConfirm } details={details}/>
                <Button type="primary" className={styles.mt_lg} onClick={this.handleClick}>导出列表</Button>
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
