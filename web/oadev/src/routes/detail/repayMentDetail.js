//还款审批&还款详情
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
import Confirm from '../../components/details/confirmRepayment';
import { chkPms,Bread } from '../../components/common';
import BreadcrumbCustom from '../../components/BreadcrumbCustom';
import cs from 'classnames';
import styles from '../style.less';
const Option = Select.Option;
const FormItem = Form.Item;

const RepayMentDetail = React.createClass({
    getInitialState(){
        return {
            ...this.props.Detail,
        };
    },
    handleConfirmClick(){
        const { RepayMent_Detail } = this.props.Detail;
        this.props.dispatch({
            type:'Detail/RepayMentConfirmQuery',
            payload:{
                isShowRepaymentConfirm:true,
                apply_id:RepayMent_Detail.apply_id
            }
        });
    },
    handlepass(event){
        const {
            getFieldDecorator,
            validateFields,
            getFieldsValue,
            getFieldValue
        } = this.props.form;

      const { ApplyID,personID } = this.props.Detail;
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
    render(){
        const { isTitleStatus,RepayMent_Detail,isShowRepaymentConfirm,ApplyID } = this.props.Detail;
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
            title = '还款详情';
            approval = '';
        }else if(isTitleStatus == "approval" && chkPms(['shen_pi']) ){
            title = '还款审批';
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
            title = '还款确认';
            const GenTable = () => <ApplyTable tabledata={tabledata}/>;
            confirm = (<ConfirmButton handleClick={this.handleConfirmClick} />);
        }
        //const GenConfirm = () => ;

        let bxmx_columns = [{
                              title: '序号',
                              dataIndex: 'name',
                              key:'name',
                              render: (text, row, index) => index + 1,
                              width:60
                            },{
                              title: '借款金额',
                              key:'money',
                              dataIndex: 'money',
                              className:cs("t-r"),
                              width:120
                            },{
                              title: '借款时间',
                              key:'time',
                              dataIndex: 'time',
                              className:cs("t-c"),
                            },{
                              title: '事由',
                              key:'des',
                              dataIndex: 'des',
                            }];


            let name = '',bank_name='',bank_id='',bank_des='',money='',des='',tips='',pics='',pdf='';
            if(Object.keys(RepayMent_Detail).length > 0){
                money = RepayMent_Detail.info.money;
                name = RepayMent_Detail.person
                bank_id = RepayMent_Detail.info.bank_card_id;
                bank_name = RepayMent_Detail.info.bank_name;
                bank_des = RepayMent_Detail.info.bank_des;
                des = RepayMent_Detail.info.des;
                tips = RepayMent_Detail.info.tips;
                pics = RepayMent_Detail.info.pics;
                pdf = RepayMent_Detail.pdf;
            }
            let  ttype = location.hash.split("?")[1].split("&")[0].split("=")[1];
            let  bread = '';
            if( ttype == "approval"){
                bread = (<BreadcrumbCustom first="待我审批" second="还款审批" furl="/waitmeapprove" />);
            }else if(ttype == "confirm"){
                bread = (<BreadcrumbCustom first="收款确认" second="还款确认" furl="/make_collections" />);
            }else{
                bread = (<BreadcrumbCustom first="还款详情" second="" furl="" />);
            }
        return(
            <Main location={location}>
                {bread}
                <Row>
                    <div className={styles.home_wrap}>
                        <Pagetitle isback='true' title={title} />
                        <StepDetail stepdata={RepayMent_Detail} />
                        <BxDetail columns={bxmx_columns} pdf={pdf} dataSource={RepayMent_Detail.info} label="还款列表" pdf={pdf} />
                        <FormItem {...formItemLayout}  label="还款金额" className="mb-sm">
                            <p style={{marginTop:5}}>{ money }元</p>
                        </FormItem>
                        <FormItem {...formItemLayout}  label="还款银行卡" className="mb-sm">
                            <p style={{marginTop:5}}>{ name }&nbsp;&nbsp;&nbsp;{ bank_name }</p>
                            <p>{ bank_id }</p>
                            <p>{ bank_des }</p>
                        </FormItem>
                        {des != null ?
                            (<FormItem {...formItemLayout}  label="备注" className="mb-sm">
                                <p style={{marginTop:5}}>{ des }</p>
                            </FormItem>)
                            : ''
                        }
                        { approval }
                        { confirm }
                        <Confirm handleClick={this.handleConfirmClick} isShowRepaymentConfirm = { isShowRepaymentConfirm } details={RepayMent_Detail}/>
                    </div>
                </Row>
            </Main>
        );
    }
});


RepayMentDetail.propTypes = {
   location: PropTypes.object,
   Detail: PropTypes.object,
   dispatch: PropTypes.func,
};

function mapStateToProps({ Detail }) {
  return { Detail };
}

export default connect(mapStateToProps)(Form.create()(RepayMentDetail));
