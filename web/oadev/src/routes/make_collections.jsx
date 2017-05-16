import React, { Component, PropTypes } from 'react';
import { routerRedux } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Button, Row, Col,message} from 'antd';

import styles from './style.less';
import Main from '../components/home/main';
import MakeCollectionSearch from '../components/make_collections/search';
import MakeCollectionsList from '../components/make_collections/list';
import ConfirmPayment from '../components/details/confirmPayment';

 const MakeCollection= React.createClass({

   render(){
        const {
            loading,
            list,
            total,
            current,
            currentItem,
            at,
            type,
            keyword,
            begin_time,
            end_time,
            currentPage,
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
            dispatch(routerRedux.push({
                pathname: '/make_collections',
                query: {
                    currentPage:currentPage,
                },
              }));
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
            if(fieldsValue.begin_end_time != null && fieldsValue.begin_end_time != undefined && fieldsValue.begin_end_time.length > 0){
              begin_time = fieldsValue.begin_end_time[0].format('YYYY-MM-DD');
              end_time = fieldsValue.begin_end_time[1].format('YYYY-MM-DD');
            }
            this.props.dispatch({
                type:'make_collections/search',
                payload: {
                    keyword:fieldsValue.keyword,
                    sorging:sorging,
                    begin_time:begin_time,
                    end_time:end_time,
                },
            });
        },
      handleReset:()=>{},
    }
        return (
            <Main location={location}>
                <Row>
                    <div className={styles.home_wrap}>
                        <h2 className={styles.mb_md}>收款确认</h2>
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






