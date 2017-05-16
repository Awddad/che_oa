import React, { PropTypes } from 'react';
import { routerRedux } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Button, Row, Col,message} from 'antd';

import styles from './style.less';
import Main from '../components/home/main';
import MysendSearch from '../components/mysend/search';
import MysendList from '../components/mysend/list';


const Mysend=React.createClass({
   render(){
        const {
            loading,
            res,
            total,
            current,
            currentItem,
            at,
            type,
            keywords,
            start_time,
            end_time,
            currentPage,
            pageCount,
            modalVisible,
            modalType,
            sorging,
        } = this.props.mysend;
        const mysendListProps ={
            total:total,
            current:current,
            loading:loading,
            currentPage:currentPage,
            type:type,
            keywords:keywords,
            start_time:start_time,
            end_time:end_time,
            dataSource:res,
            onPageChange(currentPage){
                dispatch(routerRedux.push({
                    pathname: '/mysend',
                    query: {
                        currentPage:currentPage,
                        sorging:sorging,
                    },
                }));
            },
            onSorting(sorting,filterType){
                let payload = filterType == null ? '': {
                                            type:3,
                                            ob:'',
                                            at:filterType
                                        };

                this.dispatch({
                    type: 'mysend/filtersort',
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
      const mysendSearchProps = {
          handleSearch:(fieldsValue)=>{
              let start_time = null;
              let end_time = null;
              if(fieldsValue.begin_end_time != null && fieldsValue.begin_end_time != undefined && fieldsValue.begin_end_time.length > 0){
                  start_time = fieldsValue.begin_end_time[0].format('YYYY-MM-DD');
                  end_time = fieldsValue.begin_end_time[1].format('YYYY-MM-DD');
              }
                this.props.dispatch({
                    type:'mysend/search',
                    payload: {
                        type: 3,
                        keywords:fieldsValue.keywords,
                        sorging:sorging,
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
                        <h2 className={styles.mb_md}>我发起的</h2>
                        <MysendSearch {...mysendSearchProps}/>
                        <MysendList {...mysendListProps}/>
                    </div>
                </Row>
            </Main>
        );
    }
})

Mysend.propTypes = {
  mysend: PropTypes.object,
  location: PropTypes.object,
  dispatch: PropTypes.func,
};

// 与models绑定,namespace
function mapStateToProps({ mysend }) {
  return { mysend };
}


export default connect(mapStateToProps)(Mysend);






