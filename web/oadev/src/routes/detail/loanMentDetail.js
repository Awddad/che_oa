//借款审批&借款详情
import React,{ Component,PropTypes} from 'react';
import { routerRedux } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Button,Select, Row, Col,message, Steps,Popover,Input,Modal} from 'antd';
import Main from '../../components/home/main';
import Pagetitle from '../../components/public/pagetitle';
import StepDetail from '../../components/details/stepDetail';
import BxDetail from '../../components/details/bxDetail';
import Accessory from '../../components/details/accessory';
import DetailImg from '../../components/details/detailimg';
import Approval from '../../components/details/approval';
import ConfirmButton from '../../components/details/confirmbutton';
import Confirm from '../../components/details/confirmPayment';
import { chkPms,Bread } from '../../components/common';
import cs from 'classnames';
import styles from '../style.less';
const Option = Select.Option;
const FormItem = Form.Item;

const ReimburseDetail = React.createClass({
    getInitialState(){
        if(location.hash.split("?")[1].split("&")[0].split("=")[1] == "approval"){
          Bread("借款审批","ThreeCrumb");
        }else{
          Bread("借款详情","ThreeCrumb");
        }
        return {
            ...this.props.Detail,
        };
    },
    handlepass(event){
        const {
            getFieldDecorator,
            validateFields,
            getFieldsValue,
            getFieldValue
        } = this.props.form;

      const { ApplyID,personID} = this.props.Detail;
      const formdata = { ...getFieldsValue() };

      let link = this.props;
      validateFields((errors) => {
            if (errors) {
                return;
            }else{
                let status = event.target.getAttribute("data-Status") == null ? event.target.parentNode.getAttribute("data-Status") : event.target.getAttribute("data-Status");
                switch(status){
                    case '0':
                        Modal.confirm({
                            title: '确认不通过该申请吗？',
                            content: '确认不通过后，会中止该申请的继续进行并通知申请人。',
                            iconType:'close-circle',
                            onOk() {
                                link.dispatch({
                                        type:'Detail/Approval',
                                        payload:{
                                            apply_id:ApplyID,
                                            person_id:personID,
                                            des:formdata.approval,
                                            status:status,
                                            url:'/waitmeapprove'
                                        }
                                });
                            }
                        });
                    break;
                    case '1':
                        Modal.confirm({
                            title: '通过该申请',
                            content: '确定通过该用户的申请吗？',
                            onOk() {
                                link.dispatch({
                                    type:'Detail/Approval',
                                    payload:{
                                        apply_id:ApplyID,
                                        person_id:personID,
                                        des:formdata.approval,
                                        status:status,
                                        url:'/waitmeapprove'
                                    }
                                });
                            }
                        });
                    break;
                }
            }
        });
    },
    handleConfirmClick(){
        const { Loan_Detail } = this.props.Detail;
        this.props.dispatch({
            type:'Detail/PayMentConfirmQuery',
            payload:{
                isShowPaymentConfirm:true,
                apply_id:Loan_Detail.apply_id,
                type:'loan'
            }
        });
    },
    render(){
        const {isTitleStatus,Loan_Detail,isShowPaymentConfirm,ApplyID} = this.props.Detail;
        const {
            getFieldDecorator,
            validateFields,
            getFieldsValue,
            getFieldValue
        } = this.props.form;

        const formItemLayout = {
                              labelCol: {
                                xs: { span: 24 },
                                sm: { span: 2 },
                              },
                              wrapperCol: {
                                xs: { span: 24 },
                                sm: { span: 14 },
                              },
                            };

        let title=null,approval=null,confirm=null;
        if(isTitleStatus == null){
            title = '借款详情';
            approval = '';
        }else if(isTitleStatus == "approval" && chkPms(['shen_pi'])){
            title = '借款审批';
            approval=(<div className={styles.postil}>
                        <Form>
                          <FormItem {...formItemLayout} label="审批备注">
                            {getFieldDecorator('approval', {
                              rules: [{ required: true, message: '请输入审批内容!'}]
                            })(
                              <Input type="text" placeholder="请输入" />
                            )}
                          </FormItem>
                          <FormItem >
                              <Button className={cs('ant-col-sm-offset-3','ant-col-md-offset-2','mr-md')} type="primary" data-Status="1" onClick={this.handlepass} >通过</Button>
                              <Button data-Status="0" onClick={this.handlepass}>不通过</Button>
                          </FormItem>
                        </Form>
                      </div>);
        }else if(isTitleStatus == "confirm"){
            title = '借款确认';
            const GenTable = () => <ApplyTable tabledata={tabledata}/>;
            confirm = (<ConfirmButton handleClick={this.handleConfirmClick} />);
        }


            let name = '',bank_name='',bank_id='',bank_des='',money='',des='',tips='',pics=null,pdf=null;
            if(Object.keys(Loan_Detail).length > 0){
                money = Loan_Detail.info.money;
                name = Loan_Detail.person
                bank_id = Loan_Detail.info.bank_card_id;
                bank_name = Loan_Detail.info.bank_name;
                bank_des = Loan_Detail.info.bank_des;
                des = Loan_Detail.info.des;
                tips = Loan_Detail.info.tips;
                pics = Loan_Detail.info.pics;
                pdf = Loan_Detail.pdf;
            }

        return(
            <Main location={location}>
                <Row>
                    <div className={styles.home_wrap}>
                        <Pagetitle isback='true' title={title} />
                        <StepDetail stepdata={Loan_Detail} />
                        <h2 className={cs('mt-lg','mb-md')}><strong>需审批内容</strong><a className={cs(styles.download,'ml-sm')} href={pdf}>下载审批单</a></h2>
                        <FormItem {...formItemLayout}  label="借款金额" className="mb-sm">
                            <p style={{marginTop:5}}>{ money }元</p>
                        </FormItem>
                        <FormItem {...formItemLayout}  label="借款到" className="mb-sm">
                            <p style={{marginTop:5}}>{ name }&nbsp;&nbsp;&nbsp;{ bank_name }</p>
                            <p>{ bank_id }</p>
                            <p>{ bank_des }</p>
                        </FormItem>
                        <FormItem {...formItemLayout}  label="事由" className="mb-sm">
                            <p style={{marginTop:5}}>{ des }</p>
                        </FormItem>
                        {tips != null ?
                          (<FormItem {...formItemLayout}  label="备注" className="mb-sm">
                              <p style={{marginTop:5}}>{ tips }</p>
                          </FormItem>)
                          :''
                        }
                        <DetailImg imgdata={pics} />
                        { approval }
                        { confirm }
                        <Confirm handleClick={this.handleConfirmClick} isShowPaymentConfirm = { isShowPaymentConfirm } details={Loan_Detail}/>
                    </div>
                </Row>
            </Main>
        );
    }

});

ReimburseDetail.propTypes = {
   location: PropTypes.object,
   Detail: PropTypes.object,
   dispatch: PropTypes.func,
};

function mapStateToProps({ Detail }) {
  return { Detail };
}

export default connect(mapStateToProps)(Form.create()(ReimburseDetail));
