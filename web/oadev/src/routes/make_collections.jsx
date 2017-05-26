import React, { Component, PropTypes } from 'react';
import { routerRedux } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Button, Row, Col,message} from 'antd';
import cs from 'classnames';
import styles from './style.less';
import Main from '../components/home/main';
import MakeCollectionSearch from '../components/make_collections/search';
import MakeCollectionsList from '../components/make_collections/list';
import ConfirmPayment from '../components/details/confirmPayment';
import Pagetitle from '../components/public/pagetitle';

 const MakeCollection= React.createClass({

   render(){
        const {
            loading,
            list,
            total,
            current,
            currentItem,
            at,
            sort,
            type,
            keyword,
            begin_time,
            end_time,
            currentPage,
            perPage,
            modalVisible,
            modalType,
            sorging,
        } = this.props.make_collections;

    const makeListProps ={
            total:total,
            current:current,
            loading:loading,
            type:type,
            keyword:keyword,
            begin_time:begin_time,
            end_time:end_time,
            dataSource:list,
        onPageChange(currentPage){
            dispatch(routerRedux.push({
                pathname: '/make_collections',
                query: {
                    currentPage:currentPage,
                },
              }));
            },
        onSorting(sorting){
            console.log(sorting);
            this.dispatch({
                type: 'make_collections/filtersort',
                payload: {
                    keyword:keyword,
                    current:current,
                    total: total,
                    perPage:perPage,
                    sort:sorting
                },
              });
        },
        showDetail(apply_id){
            dispatch(routerRedux.push({
                pathname:'/detail',
                query:{ apply_id }
            }))
        },

    }


  // 查询控件
    const makeSearchProps = {
        handleSearch:(fieldsValue)=>{
            let begin_time = null;
            let end_time = null;
            const { perPage }  = this.props.make_collections;
            if(fieldsValue.begin_end_time != null && fieldsValue.begin_end_time != undefined && fieldsValue.begin_end_time.length > 0){
              begin_time = fieldsValue.begin_end_time[0].format('YYYY-MM-DD');
              end_time = fieldsValue.begin_end_time[1].format('YYYY-MM-DD');
            }
            this.props.dispatch({
                type:'make_collections/search',
                payload: {
                    keyword:fieldsValue.keyword,
                    begin_time:begin_time,
                    end_time:end_time,
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
                        <Pagetitle title = '收款确认'/>
                        <MakeCollectionSearch {...makeSearchProps}/>
                        <MakeCollectionsList {...makeListProps}/>
                    </div>
                </Row>
            </Main>
        );
    }
})

MakeCollection.propTypes = {
  make_collections: PropTypes.object,
  location: PropTypes.object,
  dispatch: PropTypes.func,
};

// 与models绑定,namespace
function mapStateToProps({ make_collections }) {
  return { make_collections };
}

export default connect(mapStateToProps)(MakeCollection);






