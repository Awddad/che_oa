import React, { PropTypes } from 'react';
import { routerRedux } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Button, Row, Col,message} from 'antd';
import cs from 'classnames';
import styles from './style.less';
import Main from '../components/home/main';
import WaitmeSearch from '../components/waitme-approve/search';
import WaitmeList from '../components/waitme-approve/list';
import Pagetitle from '../components/public/pagetitle';


const Waitme = React.createClass({
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
            perPage,
            modalVisible,
            modalType,
            sorging,
        } = this.props.waitme;


        const WaitmeListProps ={
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
                    pathname: '/waitme-approve',
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
                                    at:filterType,
                                    page_size:perPage
                                };

                this.dispatch({
                    type: 'waitme/filtersort',
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
        const WaitmeSearchProps = {
            handleSearch:(fieldsValue)=>{
                let start_time = null;
                let end_time = null;
                const { type,sort,at,perPage }  = this.props.waitme;
                if(fieldsValue.begin_end_time != null && fieldsValue.begin_end_time != undefined && fieldsValue.begin_end_time.length > 0){
                    start_time = fieldsValue.begin_end_time[0].format('YYYY-MM-DD');
                    end_time = fieldsValue.begin_end_time[1].format('YYYY-MM-DD');
                }
                this.props.dispatch({
                    type:'waitme/search',
                    payload: {
                        type: type,
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
                        <Pagetitle title="待我审批" />
                        <WaitmeSearch {...WaitmeSearchProps}/>
                        <WaitmeList {...WaitmeListProps}/>
                    </div>
                </Row>
            </Main>
        );
    }
});

Waitme.propTypes = {
  waitme: PropTypes.object,
  location: PropTypes.object,
  dispatch: PropTypes.func,
};

// 与models绑定,namespace
function mapStateToProps({ waitme }) {
  return { waitme };
}


export default connect(mapStateToProps)(Waitme);






