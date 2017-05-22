import { connect } from 'dva';
import React,{ Component,PropTypes} from 'react';
import {Form,message,Icon } from 'antd';
import styles from '../../routes/reimburse.less';
import cs from 'classnames';

const AuditingLi = React.createClass({
    getInitialState(){
        return {
          ...this.props.reimBurse,
        };
    },
    handleconstClick(e){
        const index = e.target.getAttribute("data-id");
        const { constdata,constPersonal } = this.props.reimBurse;
        constdata.splice(index,1);
        this.props.dispatch({
          type: 'reimBurse/addconst',
          payload: {
            constdata:constdata,
            constPersonal:constPersonal,
            type:2
            }
        });
    },
    handlecopyClick(e){
        const index = e.target.getAttribute("data-id");
        const { copydata,constPersonal } = this.props.reimBurse;
        copydata.splice(index,1);

        this.props.dispatch({
          type: 'reimBurse/addcopy',
          payload: {
            copydata:copydata,
            constPersonal:constPersonal,
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
                    <span>{this.props.name}</span>
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

function mapStateToProps({ reimBurse }) {
  return { reimBurse };
}

export default connect(mapStateToProps)(Form.create()(AuditingLi));