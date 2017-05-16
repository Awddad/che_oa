import React, { PropTypes } from 'react';
import { routerRedux } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Button, Row, Col,message} from 'antd';
import styles from './style.less';
import Main from '../components/home/main';
import LoadDetailSearch from '../components/statistics/search';
import LoadDetailList from '../components/statistics/list';


const LoadDetail = React.createClass({
   render(){
        const {
            loading,
            info,
            total,
            current,
            currentItem,
            key,
            time,
            currentPage,
            modalVisible,
            modalType,
            sorging,
        } = this.props.Statistics;

        const detailListProps ={
            total:total,
            current:current,
            loading:loading,
            key:key,
            time:time,
            dataSource:info,
            onPageChange(currentPage){
                dispatch(routerRedux.push({
                    pathname: '/statistics',
                    query: {
                        currentPage:currentPage,
                    },
                  }));
                },
            onSorting(sorting){
                dispatch(routerRedux.push({
                    pathname: '/statistics',
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
            }
        }
      // 查询控件
      const loadSearchProps = {
          handleSearch:(fieldsValue)=>{
              let time = null;
              if(fieldsValue.begin_end_time != null && fieldsValue.begin_end_time != undefined && fieldsValue.begin_end_time.length > 0){
                  time = fieldsValue.begin_end_time[0].format('YYYY-MM-DD');
                  time = fieldsValue.begin_end_time[1].format('YYYY-MM-DD');
              }
           this.props.dispatch({
                    type:'statistics/search',
                    payload: {
                        key:fieldsValue.key,
                        sorging:sorging,
                        time:time,
                    },
                });
            },
          handleReset:()=>{},
      }
        return (
            <Main location={location}>
                <Row>
                    <div className={styles.home_wrap}>
                        <h2 className={styles.mb_md}>在借款员工明细表</h2>
                        <LoadDetailSearch {...loadSearchProps} />
                        <LoadDetailList {...detailListProps}/>
                    </div>
                </Row>
            </Main>
        );
    }
})

LoadDetail.propTypes = {
  Statistics: PropTypes.object,
  location: PropTypes.object,
  dispatch: PropTypes.func,
};

// 与models绑定,namespace
function mapStateToProps({ Statistics }) {
  return { Statistics };
}


export default connect(mapStateToProps)(LoadDetail);






