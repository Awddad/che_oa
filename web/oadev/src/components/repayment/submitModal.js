import { connect } from 'dva';
import React,{ Component,PropTypes} from 'react';
import {Form,Icon,Button,Input,Checkbox,Select,Upload,Row,Col,Modal } from 'antd';
import styles from '../../routes/reimburse.less';
import {getCoookie} from '../common';
import cs from 'classnames';
const FormItem = Form.Item;
const Option = Select.Option;


const RepayMent_Tr =  React.createClass({
    render(){
        return(
            <tr>
                <td className="t-c"> {this.props.get_money_time} </td>
                <td className="t-r">¥ {this.props.money} </td>
                <td className="t-l"> {this.props.des} </td>
            </tr>
        );
    }
});

const SubmitModal = React.createClass({
    getInitialState(){
        return {
          ...this.props.repayMent,
        };
    },
    onCancel(){
        this.props.dispatch({
            type: 'repayMent/hideModal'
        });
    },
    render(){
        const formItemLayout = {
          labelCol: {
            xs: { span: 24 },
            sm: { span: 6 },
          },
          wrapperCol: {
            xs: { span: 24 },
            sm: { span: 14 },
          },
        };

        const modalOpts = {
          visible:this.props.issubmitmodal,
          onOk: this.props.handleSubmit,
          onCancel: this.onCancel,
          width:840,
          maskClosable:false
        };

        const {carddata,constdata,copydata,CardDetail,bank_id,bank_name,selectedRows,addApplyID} = this.props.repayMent;
        let constpersonal = constdata.map(data=>data.name.split(" ")[0]).join("、");
        let copypersonal = copydata.map(data=>data.name.split(" ")[0]).join("、");
        let des = CardDetail.explain;
        const date = new Date();
        let  dateTime  = date.getFullYear() + '年' + (date.getMonth()+1) + '月' + date.getDay()+'日';
        let name = getCoookie("username");
        let department = getCoookie("department");
        let html =[],count = 0;
        if(selectedRows != undefined ){
            for(let i = 0; i < selectedRows.length; i++ ){
                count = count + selectedRows[i].money*1;
                html.push(
                    <RepayMent_Tr key={i} get_money_time={selectedRows[i].get_money_time}  money={selectedRows[i].money} des={selectedRows[i].des} />
                );
            };
        }

        return(
            <Modal title="还款确认"  {...modalOpts} >
                <div className={cs(styles.const_wrap,'mb-md')}>
                  <h1 className="mb-md">还款单</h1>
                  <table>
                    <tbody>
                     <tr>
                         <td className={styles.b_gray} width="100">日期</td>
                         <td className="t-l">{dateTime}</td>
                         <td className={styles.b_gray} width="100">单号</td>
                         <td className="t-l">{addApplyID}</td>
                     </tr>
                     <tr>
                         <td className={styles.b_gray}>部门</td>
                         <td className="t-l">{ department }</td>
                         <td className={styles.b_gray}>报销人</td>
                         <td className="t-l">{name}</td>
                     </tr>
                     <tr>
                         <td className={styles.b_gray}>开户行名称</td>
                         <td className="t-l">{bank_name}</td>
                         <td className={styles.b_gray}>银行卡号</td>
                         <td className="t-l">{bank_id}</td>
                     </tr>
                    </tbody>
                </table>
                <table>
                    <tbody>
                     <tr>
                         <td className={styles.b_gray} width="180">借款时间</td>
                         <td className={styles.b_gray} width="180">金额</td>
                         <td className={styles.b_gray}>明细</td>
                     </tr>
                     {html}
                     <tr>
                         <td className="t-c">金额合计</td>
                         <td className="t-r">¥ { count.toFixed(2) }</td>
                         <td>&nbsp;</td>
                     </tr>
                     <tr>
                         <td className={cs(styles.b_gray,"t-c")}>备注</td>
                         <td className="t-l" colSpan="2">{des}</td>
                     </tr>
                    </tbody>
                </table>
                <table>
                    <tbody>
                     <tr>
                         <td className={styles.b_gray} width="66">审批人</td>
                         <td className="t-l" width="200">{constpersonal}</td>
                         <td className={styles.b_gray} width="66">抄送人</td>
                         <td className="t-l" width="200">{copypersonal}</td>
                         <td className={styles.b_gray} width="财务确认">抄送人</td>
                         <td className="t-l">&nbsp;</td>
                     </tr>
                    </tbody>
                </table>
                </div>
            </Modal>
        );
    }
});

SubmitModal.propTypes = {
    location: PropTypes.object,
    dispatch: PropTypes.func,
}

RepayMent_Tr.propTypes = {
    location: PropTypes.object,
    dispatch: PropTypes.func,
}

function mapStateToProps({ repayMent }) {
  return { repayMent };
}

export default connect(mapStateToProps)(Form.create()(SubmitModal));