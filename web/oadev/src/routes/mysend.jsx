import React, { PropTypes } from 'react';
import { routerRedux } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Button, Row, Col,message,Modal} from 'antd';

import styles from './style.less';
import Main from '../components/home/main';
import MysendSearch from '../components/mysend/search';
import MysendList from '../components/mysend/list';

const confirm = Modal.confirm;
const Mysend=React.createClass({
    // 撤销
    showConfirm(event) {
        let { personID,status,at,sort,total,perPage } = this.props.mysend;
        let apply_id = event.target.getAttribute("data-applyid") == null ? event.target.parentNode.getAttribute("data-applyid") : event.target.getAttribute("data-applyid");
        const link = this.props;
        confirm({
            title: '确认撤销该申请吗？',
            content: '撤销该申请后，将不会继续进行审批流程',
            onOk() {
                link.dispatch({
                    type: 'mysend/revoke',
                    payload:{
                        apply_id:apply_id,
                        person_id:personID,
                        type:3,
                        at:at,
                        sort:sort,
                        status:status,
                        pageCount:total,
                        page_size:10
                    }
                });
            }
        });
    },
    render(){
        const {
            loading,
            res,
            total,
            current,
            currentItem,
            at,
            sort,
            status,
            type,
            keywords,
            start_time,
            end_time,
            currentPage,
            perPage,
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
            onSorting(sorting,filterType,filterStatus){
                let payload = {
                                    type:type,
                                    keyword: keywords,
                                    start_time: start_time,
                                    end_time: end_time,
                                    sort:sorting,
                                    at:filterType,
                                    status:filterStatus,
                                    page_size:perPage,
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
              const { type,ob,at,perPage }  = this.props.mysend;
              if(fieldsValue.begin_end_time != null && fieldsValue.begin_end_time != undefined && fieldsValue.begin_end_time.length > 0){
                  start_time = fieldsValue.begin_end_time[0].format('YYYY-MM-DD');
                  end_time = fieldsValue.begin_end_time[1].format('YYYY-MM-DD');
              }
                this.props.dispatch({
                    type:'mysend/search',
                    payload: {
                        type:type,
                        keywords:fieldsValue.keywords,
                        sort:sort,
                        at:at,
                        status:status,
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
                        <h2 className={styles.mb_md}>我发起的</h2>
                        <MysendSearch {...mysendSearchProps}/>
                        <MysendList {...mysendListProps} handleClick = {this.showConfirm}/>
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
  UserInfo:PropTypes.object,
};

// 与models绑定,namespace
function mapStateToProps({ mysend,UserInfo }) {
  return { mysend,UserInfo };
}


export default connect(mapStateToProps)(Mysend);






