import React, { PropTypes } from 'react';
import { routerRedux } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Button, Row, Col,message} from 'antd';

import styles from './style.less';
import Main from '../components/home/main';
import AlreadyApproveSearch from '../components/already-approve/search';
import AlreadyApproveList from '../components/already-approve/list';


const AlreadyApprove =React.createClass({
   render(){
        const {
            loading,
            res,
            total,
            current,
            currentItem,
            at,
            sort,
            type,
            keywords,
            start_time,
            end_time,
            currentPage,
            modalVisible,
            modalType,
            sorging,
        } = this.props.alreadyApprove;


        const alreadyListProps ={
            total:total,
            current:current,
            loading:loading,
            type:type,
            keywords:keywords,
            start_time:start_time,
            end_time:end_time,
            dataSource:res,
            filteredValue:at,
            sortOrder:sort,
            onPageChange(currentPage){
                dispatch(routerRedux.push({
                    pathname: '/already-approve',
                    query: {
                        type:type,
                        currentPage:currentPage,
                    },
                  }));
                },
                onSorting(sorting,filterType){
                    filterType = filterType || [];
                    let payload =   {
                                        type:type,
                                        keywords: keywords,
                                        start_time: start_time,
                                        end_time: end_time,
                                        sort:sorting,
                                        at:filterType
                                    };

                    this.dispatch({
                        type: 'alreadyApprove/filtersort',
                        payload: payload
                    });
                },
        }

      // 查询控件
        const alreadySearchProps = {
            handleSearch:(fieldsValue)=>{
              let start_time = null;
              let end_time = null;
              if(fieldsValue.begin_end_time != null && fieldsValue.begin_end_time != undefined && fieldsValue.begin_end_time.length > 0){
                  start_time = fieldsValue.begin_end_time[0].format('YYYY-MM-DD');
                  end_time = fieldsValue.begin_end_time[1].format('YYYY-MM-DD');
              }
                this.props.dispatch({
                    type:'alreadyApprove/search',
                    payload: {
                        type: 2,
                        keywords:fieldsValue.keywords,
                        sort:sort,
                        at:at,
                        page:1,
                        page_size:perPage,
                        start_time:start_time,
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
                        <h2 className={styles.mb_md}>我已审批</h2>
                        <AlreadyApproveSearch {...alreadySearchProps}/>
                        <AlreadyApproveList {...alreadyListProps}/>
                    </div>
                </Row>
            </Main>
        );
    }
})


AlreadyApprove.propTypes = {
  alreadyApprove: PropTypes.object,
  location: PropTypes.object,
  dispatch: PropTypes.func,
};

// 与models绑定,namespace
function mapStateToProps({ alreadyApprove }) {
  return { alreadyApprove };
}


export default connect(mapStateToProps)(AlreadyApprove);






