import React, { PropTypes } from 'react';
import { routerRedux } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Button, Row, Col,message} from 'antd';

import styles from './style.less';
import Main from '../components/home/main';
import CcsendSearch from '../components/ccsend/search';
import CcsendList from '../components/ccsend/list';


const Ccsend = React.createClass({
   render(){
        const {
            loading,
            res,
            total,
            current,
            currentItem,
            at,
            type,
            status,
            keywords,
            start_time,
            end_time,
            currentPage,
            showDetail,
            modalVisible,
            modalType,
            sorging,
        } = this.props.ccsend;

        const ccsendListProps ={
            total:total,
            current:current,
            loading:loading,
            type:type,
            keywords:keywords,
            start_time:start_time,
            end_time:end_time,
            dataSource:res,
            onPageChange(currentPage){
                dispatch(routerRedux.push({
                    pathname: '/ccsend',
                    query: {
                        currentPage:currentPage,
                        sorging:sorging,
                    },
                }));
            },
            onSorting(sorting,filterType,filterStatus){
                let payload = filterType == null ? '': {
                                            type:4,
                                            ob:'',
                                            at:filterType,
                                            status:filterStatus,
                                        };

                this.dispatch({
                    type: 'ccsend/filtersort',
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
        //console.log(ccsendListProps);
        // 查询控件
        const ccsendSearchProps = {
            handleSearch:(fieldsValue)=>{
              let start_time = null;
              let end_time = null;
              if(fieldsValue.begin_end_time != null && fieldsValue.begin_end_time != undefined && fieldsValue.begin_end_time.length > 0){
                  start_time = fieldsValue.begin_end_time[0].format('YYYY-MM-DD');
                  end_time = fieldsValue.begin_end_time[1].format('YYYY-MM-DD');
                }
              //console.log(fieldsValue.keywords);
                this.props.dispatch({
                    type:'ccsend/search',
                    payload: {
                        type: 4,
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
                        <h2 className={styles.mb_md}>抄送给我</h2>
                        <CcsendSearch {...ccsendSearchProps}/>
                        <CcsendList {...ccsendListProps}/>
                    </div>
                </Row>
            </Main>
        );
    }
});

Ccsend.propTypes = {
  ccsend: PropTypes.object,
  location: PropTypes.object,
  dispatch: PropTypes.func,
};

// 与models绑定,namespace
function mapStateToProps({ ccsend }) {
  return { ccsend };
}

export default connect(mapStateToProps)(Ccsend);






