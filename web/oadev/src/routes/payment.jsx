import React, { Component, PropTypes } from 'react';
import { routerRedux } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Button, Row, Col,message} from 'antd';
import cs from 'classnames';
import styles from './style.less';
import Main from '../components/home/main';
import PaymentSearch from '../components/payment/search';
import PaymentList from '../components/payment/list';
import Pagetitle from '../components/public/pagetitle';

const Payment =React.createClass({
   render(){
        const {
            loading,
            list,
            total,
            current,
            currentItem,
            type,
            keyword,
            sort,
            begin_time,
            end_time,
            repayment,
            currentPage,
            perPage,
            modalVisible,
            modalType,
            sorging,
        } = this.props.payment;

    const paymentListProps ={
        total:total,
        current:current,
        loading:loading,
        keyword:keyword,
        sortOrder:sort,
        begin_time:begin_time,
        end_time:end_time,
        dataSource:list,

        onPageChange(currentPage){
            dispatch(routerRedux.push({
                pathname: '/payment',
                query: {
                    type:type,
                    currentPage:currentPage,
                },
              }));
            },
            onSorting(sorting,filterType){
                let payload =   {
                                    type:filterType,
                                    keyword: keyword,
                                    begin_time: begin_time,
                                    end_time: end_time,
                                    sort:sorting,
                                    type:filterType,
                                    perPage:perPage,
                                };

                this.dispatch({
                    type: 'payment/filtersort',
                    payload: payload
                });
            },
        showDetail(apply_id){
            dispatch(routerRedux.push({
                pathname:'/detail',
                query:{ apply_id }
            }))
        }

    }
      // 查询控件
      const paymentSearchProps = {
            handleSearch:(fieldsValue)=>{
              let begin_time = null;
              let end_time = null;
              const { perPage }  = this.props.payment;
              if(fieldsValue.begin_end_time != null && fieldsValue.begin_end_time != undefined && fieldsValue.begin_end_time.length > 0){
                  begin_time = fieldsValue.begin_end_time[0].format('YYYY-MM-DD');
                  end_time = fieldsValue.begin_end_time[1].format('YYYY-MM-DD');
                }
                console.log(end_time);
                this.props.dispatch({
                    type:'payment/search',
                    payload: {
                        keyword:fieldsValue.keyword,
                        begin_time:begin_time==null?'':begin_time,
                        end_time: end_time == null?'':end_time,
                        currentPage:1,
                        perPage:perPage,
                    },
                });
            },
            handleReset:()=>{},
      }
        return (
            <Main location={location}>
                <Row>
                    <div className={styles.home_wrap}>
                        <Pagetitle title = '付款确认'/>
                        <PaymentSearch {...paymentSearchProps}/>
                        <PaymentList {...paymentListProps}/>
                    </div>
                </Row>
            </Main>
        );
    }
})






Payment.propTypes = {
  payment: PropTypes.object,
  location: PropTypes.object,
  dispatch: PropTypes.func,
};

function mapStateToProps({ payment }) {
  return {payment};
}

export default connect(mapStateToProps)(Payment);






