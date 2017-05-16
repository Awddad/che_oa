//还款审批&还款详情
import React,{ Component,PropTypes} from 'react';
import { routerRedux } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Button,Select, Row, Col,message, Steps,Popover} from 'antd';
import Main from '../../components/home/main';
import Pagetitle from '../../components/public/pagetitle';
import StepDetail from '../../components/details/stepDetail';
import BxDetail from '../../components/details/bxDetail';
import Accessory from '../../components/details/accessory';
import DetailImg from '../../components/details/detailimg';
import Approval from '../../components/details/approval';
import ConfirmButton from '../../components/details/confirmbutton';
import Confirm from '../../components/details/confirmRepayment';
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
    render(){
        const { isTitleStatus,RepayMent_Detail,isShowRepaymentConfirm,ApplyID } = this.props.Detail;
        let title=null,approval=null,confirm=null;
        if(isTitleStatus == null){
            title = '还款详情';
            approval = '';
        }else if(isTitleStatus == "approval"){
            title = '还款审批';
            approval = (<Approval ApplyID={ ApplyID } url="/repayment" />);
        }else if(isTitleStatus == "confirm"){
            title = '还款确认';
            const GenTable = () => <ApplyTable tabledata={tabledata}/>;
            confirm = (<ConfirmButton handleClick={this.handleConfirmClick} />);
        }
        const GenConfirm = () => <Confirm handleClick={this.handleConfirmClick} isShowRepaymentConfirm = { isShowRepaymentConfirm } details={RepayMent_Detail}/>;
        const formItemLayout = {
                              labelCol: {
                                xs: { span: 24 },
                                sm: { span: 3 },
                                md: { span: 2 },
                              },
                              wrapperCol: {
                                xs: { span: 24 },
                                sm: { span: 14 },
                              },
                            };

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


            let name = '',bank_name='',bank_id='',bank_des='',money='',des='',tips='';
            if(Object.keys(RepayMent_Detail).length > 0){
                money = RepayMent_Detail.info.money;
                name = RepayMent_Detail.person
                bank_id = RepayMent_Detail.info.bank_card_id;
                bank_name = RepayMent_Detail.info.bank_name;
                bank_des = RepayMent_Detail.info.bank_des;
                des = RepayMent_Detail.info.des;
                tips = RepayMent_Detail.info.tips;
            }




        return(
            <Main location={location}>
                <Row>
                    <div className={styles.home_wrap}>
                        <Pagetitle title={title} />
                        <StepDetail stepdata={RepayMent_Detail} />
                        <BxDetail columns={bxmx_columns} dataSource={RepayMent_Detail.info} label="还款列表" />

                        <h2 className={cs('mt-md','mb-md')}><strong>需审批内容</strong><a className={cs(styles.download,'ml-sm')} href="#">下载审批</a></h2>
                        <FormItem {...formItemLayout}  label="借款金额" className="mb-sm">
                            <p style={{marginTop:5}}>{ money }元</p>
                        </FormItem>
                        <FormItem {...formItemLayout}  label="借款到" className="mb-sm">
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
                        <DetailImg imgdata={ RepayMent_Detail.pics } />
                        { approval }
                        { confirm }
                        <GenConfirm />
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
