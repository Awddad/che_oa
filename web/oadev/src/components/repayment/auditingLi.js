import { connect } from 'dva';
import React,{ Component,PropTypes} from 'react';
import {Form,message,Icon } from 'antd';
import styles from '../../routes/reimburse.less';
import cs from 'classnames';

const AuditingLi = React.createClass({
    getInitialState(){
        return {
          ...this.props.repayMent,
        };
    },
    handleconstClick(e){
        const index = e.target.getAttribute("data-id");
        const { constdata,constPersonal } = this.props.repayMent;
        constdata.splice(index,1);

        this.props.dispatch({
          type: 'repayMent/addconst',
          payload: {
            constdata:constdata,
            type:2
            }
        });
    },
    handlecopyClick(e){
        const index = e.target.getAttribute("data-id");
        const { copydata,copyPersonal} = this.props.repayMent;
        copydata.splice(index,1);

        this.props.dispatch({
          type: 'repayMent/addcopy',
          payload: {
            copydata:copydata,
            type:2
            }
        });
    },
    render(){
        const style={
              display: this.props.imgvisiable ? "block" : "none"
            }

        return(
            <li>
                <div className={styles.approval_left}>
                    <span data-key={this.props.name}>{this.props.name}</span>
                    <a href="javascript:;" data-id={this.props.dataid} onClick={this.props.litype==1 ? this.handleconstClick : this.handlecopyClick}>删除</a>
                </div>
                <div style={style} className={styles.approval_right}>
                    <Icon type="caret-right" />
                </div>
            </li>
        );
    }
});

AuditingLi.propTypes = {
    location: PropTypes.object,
    dispatch: PropTypes.func
}

function mapStateToProps({ repayMent }) {
  return { repayMent };
}

export default connect(mapStateToProps)(Form.create()(AuditingLi));