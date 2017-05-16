import { connect } from 'dva';
import React,{ Component,PropTypes} from 'react';
import {Form,Icon,Button,Input,Checkbox,Select,Upload,Row,Col,Modal } from 'antd';
import styles from '../../routes/reimburse.less';
import cs from 'classnames';
const FormItem = Form.Item;
const Option = Select.Option;

const SubmitModal = React.createClass({
    getInitialState(){
        return {
          ...this.props.applyLoan,
        };
    },
    onCancel(){
        this.props.dispatch({
            type: 'applyLoan/hideModal'
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

        const {carddata,constdata,copydata,CardDetail,bank_id,bank_name} = this.props.applyLoan;
        let constpersonal = constdata.map(data=>data.name.split(" ")[0]).join("、");
        let copypersonal = copydata.map(data=>data.name.split(" ")[0]).join("、");
        let des = CardDetail.des;
        let money = CardDetail.money;
        let tips = CardDetail.tips;
        const date = new Date();
        let  dateTime  = date.getFullYear() + '年' + (date.getMonth()+1) + '月' + date.getDay()+'日';

        return(
            <Modal title="申请借款确认"  {...modalOpts} >
                <div className={cs(styles.const_wrap,'mb-md')}>
                  <h1 className="mb-md">报销单</h1>
                  <table>
                    <tbody>
                         <tr>
                             <td className={styles.b_gray} width="100">日期</td>
                             <td className="t-l">{dateTime}</td>
                             <td className={styles.b_gray} width="100">单号</td>
                             <td className="t-l">2017050401001</td>
                         </tr>
                         <tr>
                             <td className={styles.b_gray}>部门</td>
                             <td className="t-l">南京汽车销售-中规车一区-涟水店</td>
                             <td className={styles.b_gray}>报销人</td>
                             <td className="t-l">马聪</td>
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
                             <td className={styles.b_gray} width="180">借款金额</td>
                             <td className={styles.b_gray}>事由</td>
                         </tr>
                         <tr>
                             <td className={cs('t-c',styles.b_gray)}>¥ {money}</td>
                             <td className="t-l">{des}</td>
                         </tr>
                         <tr>
                             <td className={cs('t-c',styles.b_gray)}>备注</td>
                             <td className="t-l">{tips}</td>
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

function mapStateToProps({ applyLoan }) {
  return { applyLoan };
}

export default connect(mapStateToProps)(Form.create()(SubmitModal));