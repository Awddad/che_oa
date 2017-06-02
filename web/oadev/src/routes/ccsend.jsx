import React, { PropTypes } from 'react';
import { routerRedux } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Button, Row, Col,message} from 'antd';
import cs from 'classnames';
import styles from './style.less';
import Main from '../components/home/main';
import CcsendSearch from '../components/ccsend/search';
import CcsendList from '../components/ccsend/list';
import Pagetitle from '../components/public/pagetitle';
import BreadcrumbCustom from '../components/BreadcrumbCustom';


const Ccsend = React.createClass({
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
            filteredValue:at,
            sortOrder:sort,
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
            onSorting(sorting,filterType){
                let payload =   {
                                    type:type,
                                    keywords: keywords,
                                    start_time: start_time,
                                    end_time: end_time,
                                    sort:sorting,
                                    at:filterType,
                                };

                this.dispatch({
                    type: 'ccsend/filtersort',
                    payload: payload
                });
            },
        }
        // 查询控件
        const ccsendSearchProps = {
            handleSearch:(fieldsValue)=>{
              let start_time = null;
              let end_time = null;
              const {perPage} = this.props.ccsend;
              if(fieldsValue.begin_end_time != null && fieldsValue.begin_end_time != undefined && fieldsValue.begin_end_time.length > 0){
                  start_time = fieldsValue.begin_end_time[0].format('YYYY-MM-DD');
                  end_time = fieldsValue.begin_end_time[1].format('YYYY-MM-DD');
                }
                this.props.dispatch({
                    type:'ccsend/search',
                    payload: {
                        type: 4,
                        keywords:fieldsValue.keywords,
                        sort:sort,
                        at:at,
                        page:1,
                        start_time:start_time,
                        end_time:end_time,
                        page_size:perPage
                    },
                });
            },
            handleReset:()=>{},

        }

        return (
            <Main location={location}>
                <BreadcrumbCustom first="抄送给我" second="" furl="" />
                <Row>
                    <div className={styles.home_wrap}>
                        <Pagetitle title = '抄送给我'/>
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






