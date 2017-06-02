import { connect } from 'dva';
import React,{ Component,PropTypes} from 'react';
import {Form,Icon,Button,Input,Checkbox,Select,Upload,Row,Col,Modal } from 'antd';
import styles from '../../routes/reimburse.less';
import cs from 'classnames';
import { DateTime } from '../common'
const FormItem = Form.Item;
const Option = Select.Option;

const RepayMent_Tr =  React.createClass({
    render(){
        return(
            <tr>
                <td className="t-c"> {this.props.type_name} </td>
                <td className="t-r">¥ {(this.props.money*1).toFixed(2)} </td>
                <td className="t-l"> {this.props.des ==''?'--':this.props.des} </td>
            </tr>
        );
    }
});


const SubmitModal = React.createClass({
    getInitialState(){
        return {
          ...this.props.reimBurse,
          confirmLoad:false
        };
    },
    componentDidMount(){
    },
    onCancel(){
        this.props.dispatch({
            type: 'reimBurse/hideModal'
        });
    },
    handleSubmit(){
        let { tabledata,CardDetail,constdata,copydata,addApplyID,issubmitmodal } = this.props.reimBurse;

        const approval_persons = [],copy_person=[],approval_p={},copy_p={};

        for(let i =0; i<constdata.length;i++){
              approval_persons.push({"person_id": constdata[i].id,"person_name":constdata[i].name,"steep":(i+1)});
        }

        for(let i =0; i<copydata.length;i++){
              copy_person.push({"person_id": copydata[i].id,"person_name":copydata[i].name});
        }

        let files=null,file=null,pics = '',pic=null;
        if(CardDetail.file != null){
              files = CardDetail.file.fileList.map(data => data.response.data[0]);
        }
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
              type: 'reimBurse/create',
              payload: {
                  bank_card_id:(CardDetail.code).split(" ")[1],
                  bank_name:(CardDetail.code).split(" ")[0],
                  bank_name_des:(CardDetail.code).split(" ")[2],
                  bao_xiao_list:tabledata,
                  approval_persons:approval_persons,
                  copy_person:copy_person,
                  fujian:files,
                  pics:pics,
                  apply_id:addApplyID,
                  urltype:1
              }
        });
    },
    render(){
        let { issubmitmodal } = this.props.reimBurse;
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
          visible:issubmitmodal,
          onOk: this.handleSubmit,
          onCancel: this.onCancel,
          width:840,
          maskClosable:false
        };

        const {tabledata,carddata,constdata,copydata,CardDetail,bank_id,bank_name,addApplyID,department } = this.props.reimBurse;
        let constpersonal = constdata.map(data=>data.name.split(" ")[0]).join("、");
        let copypersonal = copydata.map(data=>data.name.split(" ")[0]).join("、");
        let bxname = localStorage.getItem("username");

        let html =[],count = 0;
        if(tabledata != undefined ){
            for(let i = 0; i < tabledata.length; i++ ){
                count = count + tabledata[i].money*1;
                html.push(
                    <RepayMent_Tr key={i} type_name={tabledata[i].type_name}  money={tabledata[i].money} des={tabledata[i].des} />
                );
            };
        }

        return(
            <Modal title="报销确认"  {...modalOpts} confirmLoading={this.state.confirmLoad} >
                <div className={cs(styles.const_wrap,'mb-md')}>
                  <h1 className="mb-md">报销单</h1>
                  <table>
                    <tbody>
                     <tr>
                         <td className={styles.b_gray} width="100">日期</td>
                         <td className="t-l">{DateTime()}</td>
                         <td className={styles.b_gray} width="100">单号</td>
                         <td className="t-l">{ addApplyID }</td>
                     </tr>
                     <tr>
                         <td className={styles.b_gray}>部门</td>
                         <td className="t-l">{ department }</td>
                         <td className={styles.b_gray}>报销人</td>
                         <td className="t-l">{ bxname }</td>
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
                         <td className={styles.b_gray} width="180">类别</td>
                         <td className={styles.b_gray} width="180">金额</td>
                         <td className={styles.b_gray}>明细</td>
                     </tr>
                     {html}
                     <tr>
                         <td className="t-c">金额合计</td>
                         <td className="t-r">¥ {count.toFixed(2)}</td>
                         <td>&nbsp;</td>
                     </tr>
                    </tbody>
                </table>
                <table>
                    <tbody>
                     <tr>
                         <td className={styles.b_gray} width="66">审批人</td>
                         <td className="t-l" width="200">{constpersonal}</td>
                         <td className={styles.b_gray} width="66">抄送人</td>
                         <td className="t-l" width="200">{copypersonal==''?'--':copypersonal}</td>
                         <td className={styles.b_gray} width="80">财务确认</td>
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
    reimBurse:PropTypes.object,
    ApplyIDuserInfo:PropTypes.object,
}

function mapStateToProps({ reimBurse}) {
  return { reimBurse };
}

export default connect(mapStateToProps)(Form.create()(SubmitModal));