import { connect } from 'dva';
import React from 'react';
import {Icon,Pagination} from 'antd';

const PagiNation = React.createClass({
    render(){
        return (
            <Pagination size="small" total={50} showSizeChanger showQuickJumper onChange={this.props.paginationChange}/>

        )
    }
})

export default  Pagetitle;
