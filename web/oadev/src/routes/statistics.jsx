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
            department,
            xz_department,
            currentPage,
            perPage,
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
            department:department,
            dataSource:info,
           /* onPageChange(currentPage){
                dispatch(routerRedux.push({
                    pathname: '/statistics',
                    query: {
                        currentPage:currentPage,
                    },
                  }));
            },*/
            onSorting(sorting){
                let payload =   {
                                    key: key,
                                    xz_department:xz_department,
                                    time:time,
                                    total:total,
                                    current:current,
                                    sort:sorting,
                                    pageSize:perPage,
                                };
                this.dispatch({
                    type: 'Statistics/filtersort',
                    payload: payload
                });
            },
        }
      // 查询控件
        const loadSearchProps = {
            handleSearch:(fieldsValue)=>{
              let time = null;
              let orgId = null;
                if(fieldsValue.begin_end_time != null && fieldsValue.begin_end_time != undefined && fieldsValue.begin_end_time.length > 0){
                  time = fieldsValue.begin_end_time[0].format('YYYY-MM-DD');
                  time = fieldsValue.begin_end_time[1].format('YYYY-MM-DD');
                }

                this.props.dispatch({
                    type:'Statistics/search',
                    payload: {
                        key:fieldsValue.key,
                        orgId:fieldsValue.department,
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






