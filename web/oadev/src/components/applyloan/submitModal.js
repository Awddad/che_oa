import { connect } from 'dva';
import React,{ Component,PropTypes} from 'react';
import {Form,Icon,Button,Input,Checkbox,Select,Upload,Row,Col,Modal } from 'antd';
import styles from '../../routes/reimburse.less';
import { getCookie,DateTime } from '../common';
import cs from 'classnames';
const FormItem = Form.Item;
const Option = Select.Option;

const SubmitModal = React.createClass({
    getInitialState(){
        return {
          ...this.props.applyLoan,
          confirmLoad:false
        };
    },
    onCancel(){
        this.props.dispatch({
            type: 'applyLoan/hideModal'
        });
    },
    handleSubmit(){//借款申请提交
        let { CardDetail,constdata,copydata,addApplyID } = this.props.applyLoan;
        let pic = null, pics = "";
        if(CardDetail.pics != null){
              pic = CardDetail.pics.fileList.map(data => data.response.data);
              for(let i=0;i<pic.length;i++){
                  if(i == pic.length-1){
                    pics += pic[i];
                  }else{
                    pics += pic[i]+','
                  }
              }
        }

        this.setState({
            confirmLoad:true
        });

        this.props.dispatch({
            type: 'applyLoan/create',
            payload: {
                apply_id:addApplyID,
                money:CardDetail.money,
                des:CardDetail.des,
                approval_persons:constdata.map(data => data.id),
                copy_person:copydata.map(data => data.id),
                bank_card_id:(CardDetail.code).split(" ")[1],
                bank_name:(CardDetail.code).split(" ")[0],
                tips:CardDetail.tips,
                pics:pics,
                urltype:2,
                constdata:constdata,
                copydata:copydata,
            }
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

        const {carddata,constdata,copydata,CardDetail,bank_id,bank_name,addApplyID,issubmitmodal} = this.props.applyLoan;
        const modalOpts = {
          visible:issubmitmodal,
          onOk: this.handleSubmit,
          onCancel: this.onCancel,
          width:840,
          maskClosable:false
        };


        let constpersonal = constdata.map(data=>data.name.split(" ")[0]).join("、");
        let copypersonal = copydata.map(data=>data.name.split(" ")[0]).join("、");
        let des = CardDetail.des;
        let money = CardDetail.money;
        let tips = CardDetail.tips;
        let name = getCookie("username") || '';
        let department = getCookie("department") || '';

        return(
            <Modal title="申请借款确认"  {...modalOpts} confirmLoading={this.state.confirmLoad}>
                <div className={cs(styles.const_wrap,'mb-md')}>
                  <h1 className="mb-md">借款单</h1>
                  <table>
                    <tbody>
                         <tr>
                             <td className={styles.b_gray} width="100">日期</td>
                             <td className="t-l">{DateTime()}</td>
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
                             <td className={styles.b_gray} width="180">借款金额</td>
                             <td className={styles.b_gray}>事由</td>
                         </tr>
                         <tr>
                             <td className={cs('t-c')}>¥ {money}</td>
                             <td className="t-l">{des}</td>
                         </tr>
                         <tr>
                             <td className={cs('t-c',styles.b_gray)}>备注</td>
                             <td className="t-l">{tips == null ? '--':tips}</td>
                         </tr>
                    </tbody>
                </table>
                <table>
                    <tbody>
                        <tr>
                            <td className={styles.b_gray} width="66">审批人</td>
                            <td className="t-l" width="200">{constpersonal}</td>
                            <td className={styles.b_gray} width="66">抄送人</td>
                            <td className="t-l" width="200">{copypersonal == ''?'--':copypersonal}</td>
                            <td className={styles.b_gray}>财务确认</td>
                            <td className="t-l">财务确认</td>
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