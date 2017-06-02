import { connect } from 'dva';
import React,{ Component,PropTypes} from 'react';
import {Form,Icon,Button,Input,Checkbox,Select,Upload,Row,Col,Modal,message,Table } from 'antd';
import Main from '../components/home/main';
import Pagetitle from '../components/public/pagetitle';
import AuditingLi from '../components/repayment/auditingLi';
import AddCardModal from '../components/repayment/addCardModal';
import AddConstModal from '../components/repayment/addConstModal';
import AddCopyModal from '../components/repayment/addCopyModal';
import SubmitModal from '../components/repayment/submitModal';
import {Bread} from '../components/common';
import BreadcrumbCustom from '../components/BreadcrumbCustom';
import { routerRedux } from 'dva/router';
import styles from './reimburse.less';
import cs from 'classnames';
const Option = Select.Option;
const FormItem = Form.Item;
import WebStorage from 'react-webstorage';
const webStorage = new WebStorage(window.localStorage || window.sessionStorage);

const RepayMent = React.createClass({
  getInitialState(){
    //Bread("报销管理","OneCrumb");
    Bread("申请还款","OneCrumb");
    return {
      ...this.props.repayMent,
      selectedRows:[]
    };
  },
  componentDidMount() {

  },
    //银行卡添加弹窗
  showcardModal(){
    this.props.dispatch({
        type: 'repayMent/modelHandle',
        payload: {
          isshowcardmodal:true,
          modalIndex: 0
        }
    });
  },

  //审批人选择弹窗
  showconstModal(){
    const { constPersonal } = this.props.repayMent;
    this.props.dispatch({
        type: 'repayMent/modelHandle',
        payload: {
          isshowconstmodal:true,
          modalIndex: 2,
          constPersonal:constPersonal
        }
    });
  },

  //抄送人选择弹窗
  showcopyModal(){
    const { copyPersonal } = this.props.repayMent;
    this.props.dispatch({
        type: 'repayMent/modelHandle',
        payload: {
          isshowcopymodal:true,
          modalIndex: 3,
          copyPersonal:copyPersonal,
        }
    });
  },
  //提交还款按钮显示弹窗
  showsubmitModal(){
    const {
        getFieldDecorator,
        validateFields,
        getFieldsValue,
        getFieldValue
    } = this.props.form;

    const CardDetail = { ...getFieldsValue() };
    const {constdata,copydata,selectedRows} = this.props.repayMent;

    validateFields((errors) => {
            if (errors) {
                return;
            }else{

                if( selectedRows != undefined && CardDetail.code != undefined && constdata.length > 0 ){
                    this.props.dispatch({
                        type: 'repayMent/ApplyIDquery',
                        payload: {
                          issubmitmodal:true,
                          modalIndex: 0,
                          CardDetail:CardDetail,
                          bank_name:CardDetail.code.split(" ")[0],
                          bank_id:CardDetail.code.split(" ")[1],
                          type:3,
                        }
                    });
                }else{
                  message.error("请填写完必选项再提交!");
                }
            }
    });
  },

  btnhandleCancel(){
      this.props.form.resetFields();
      const {constdata,copydata} = this.props.repayMent;
      constdata.length = 0;
      copydata.length = 0;
      this.props.dispatch({
          type:'repayMent/modelHandle',
          payload:{
              modalIndex:0,
              constdata:constdata,
              copydata:copydata
          }
      });
      webStorage.setItem("menuKey", "/adminhome");
      this.props.dispatch(routerRedux.push("/adminhome"));
  },
  render(){
    const cardmodalProps = Math.floor(Math.random()*200000);
    const constmodalProps = Math.floor(Math.random()*300000);
    const copymodalProps = Math.floor(Math.random()*400000);
    const submitmodalProps = Math.floor(Math.random()*500000);

    const { isshowcardmodal,isshowconstmodal,isshowcopymodal,issubmitmodal,constCard,constdata,copydata,selectedRows,BackList } = this.props.repayMent;
    const  cardOptions = constCard.map(data =><Option key={cardmodalProps} value={ data.bank_name + " " +data.card_id+" "+data.bank_des}>{data.bank_name+'-'+data.card_id}</Option>);

    //审批人
    const auditingLi =  constdata.map(function(data,index) {
                              return (
                                  <AuditingLi key={index} dataid={index} id={data.id} imgvisiable={true} litype="1" data-key="" name={data.name} />
                              );
                          });
    //抄送人
    const copyLi =  copydata.map(function(data,index) {
                              return (
                                  <AuditingLi key={index} dataid={index} id={data.id} imgvisiable={false} litype="2" name={data.name} />
                              );
                          });

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


    const { getFieldDecorator } = this.props.form;

    const columns = [{
          title: '序号',
          dataIndex: 'name',
          key:'name',
          width:60,
          render: (text, row, index) => index + 1}
        , {
          title: '借款金额',
          key:'money',
          dataIndex: 'money',
          width:120,
          className:cs("t-r"),
        }, {
          title: '借款时间',
          key:'get_money_time',
          dataIndex: 'get_money_time',
          width:150,
        },{
          title: '事由',
          key:'des',
          dataIndex: 'des',
          width:250
        }];

    const rowSelection = {
          onChange: (selectedRowKeys, selectedRows) => {
            selectedRows =  selectedRows;
            this.props.dispatch({
                type:"repayMent/modelHandle",
                payload:{
                   selectedRows:selectedRows,
                   modalIndex:0
                }
            });
          },
          getCheckboxProps: record => ({
            disabled: record.name === 'Disabled User',
          }),
        };

    /*const dataSource = [{
      Key:1,
      money:'1111',
      time:'2016-12-5',
      des:'借钱'
    }]*/


    const dataSource = BackList || [];
    let count = 0;
    if(dataSource.length > 0){
          for(let i=0; i<dataSource.length;i++){
              count = count + dataSource[i].money *1;
          }
    }

    const GenconstPerson = () => <ApprovalPerson handleClick={this.showconstModal} approvalPerson={auditingLi} />;
    const GencopyPerson = () => <ApprovalPerson handleClick={this.showcopyModal} approvalPerson={copyLi} />;


    return (
      <Main location={location}>
        <BreadcrumbCustom first="申请还款" second="" furl="" />
        <Row>
          <AddCardModal key={cardmodalProps} isshowcardmodal = {isshowcardmodal} />
          <AddConstModal key={constmodalProps} title="审批人" isshowconstmodal = {isshowconstmodal}/>
          <AddCopyModal key={copymodalProps} title="抄送人" isshowcopymodal = {isshowcopymodal} />
          <SubmitModal key={submitmodalProps} issubmitmodal = {issubmitmodal} handleSubmit={this.handleSubmit} />

          <Form>
            <Pagetitle isback='true' title = '还款申请表'/>
            <FormItem {...formItemLayout} className="labelt" label="选择待还借款">
                <Table className={cs("ant-col-sm-24","zstable")} size="middle" bordered rowSelection={rowSelection} columns={columns} dataSource={dataSource} pagination={false} rowKey={record => record.index} footer={() => (<table><tbody><tr><td width="122">合计</td><td width="104" className="t-r">{count.toFixed(2)}</td><td colSpan="3"></td></tr></tbody></table>)} />
            </FormItem>
            <FormItem {...formItemLayout} label="还款银行卡">
                {getFieldDecorator('code', {
                    rules: [{ required: true, message: '请选择银行卡!'}]
                })(
                    <Select className="f-l" showSearch style={{ width: '60%' }} placeholder="请选择">
                      {cardOptions}
                    </Select>
                )}
                <a className={cs('ml-sm','ant-col-sm-7')} href="javascript:;" onClick={this.showcardModal}>需要报销到其他银行卡？</a>
            </FormItem>
            <FormItem {...formItemLayout} label="说明">
                {getFieldDecorator('explain')(
                   <Input placeholder="请输入说明" type="textarea" rows="4" />
                )}
            </FormItem>
            <h3 className={cs("mt-md","mb-md")}>审批人和抄送人</h3>
            <FormItem {...formItemLayout} label="审批人" className="labelt">
                <GenconstPerson />
            </FormItem>
            <FormItem {...formItemLayout} label="抄送人" >
                <GencopyPerson />
            </FormItem>
            <FormItem>
                   <Button className={cs('mt-md','mb-md','ant-col-sm-offset-2')} type="primary" onClick={this.showsubmitModal}>确定</Button>
                   <Button className={cs('mt-md','mb-md','ml-md')} onClick={this.btnhandleCancel}>取消</Button>
            </FormItem>
          </Form>
        </Row>
      </Main>
    )
  }
});


const ApprovalPerson = React.createClass({
    render(){
          let person = this.props.approvalPerson == null ? "": this.props.approvalPerson;
        return (
            <div className={styles.approval_wrap} >
                <ul>
                  {person}
                  {
                    person == null ?
                      (<li className={styles.add_approval} onClick={this.props.handleClick} >
                          <Icon type="plus" />
                      </li>)
                    :
                      person.length < 5 ?
                        (<li className={styles.add_approval} onClick={this.props.handleClick} >
                            <Icon type="plus" />
                        </li>)
                        :""
                  }
                 </ul>
            </div>
        )
    }
});


RepayMent.propTypes = {
   location: PropTypes.object,
   repayMent: PropTypes.object,
   dispatch: PropTypes.func,
};

function mapStateToProps({ repayMent }) {
  return { repayMent };
}

export default connect(mapStateToProps)(Form.create()(RepayMent));
