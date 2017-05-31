import { connect } from 'dva';
import React,{ Component,PropTypes} from 'react';
import {Form,Icon,Button,Input,Checkbox,Select,Upload,Row,Col,Modal,message } from 'antd';
import Main from '../components/home/main';
import Pagetitle from '../components/public/pagetitle';
import AddCardModal from '../components/applyloan/addCardModal';
import AddConstModal from '../components/applyloan/addConstModal';
import AddCopyModal from '../components/applyloan/addCopyModal';
import AuditingLi from '../components/applyloan/auditingLi';
import SubmitModal from '../components/applyloan/submitModal';
import {Bread} from '../components/common';
import { routerRedux } from 'dva/router';
import styles from './reimburse.less';
import cs from 'classnames';
const Option = Select.Option;
const FormItem = Form.Item;
import WebStorage from 'react-webstorage';
const webStorage = new WebStorage(window.localStorage || window.sessionStorage);



const acceptImgFormat = 'jpg,jpeg,png,gif'; //上传图片格式

const ApplyLoan = React.createClass({
  getInitialState(){
    //Bread("报销管理","OneCrumb");
    Bread("申请借款","OneCrumb");
    return {
      ...this.props.applyLoan,
      imgfileList:[],
      previewVisible: false,
      previewImage: '',
    };
  },
  componentDidMount() {

  },

  //银行卡添加弹窗
  showcardModal(){
    this.props.dispatch({
        type: 'applyLoan/modelHandle',
        payload: {
          isshowcardmodal:true,
          modalIndex: 0
        }
    });
  },

  //审批人选择弹窗
  showconstModal(){
    const { constPersonal } = this.props.applyLoan;
    this.props.dispatch({
        type: 'applyLoan/modelHandle',
        payload: {
          isshowconstmodal:true,
          modalIndex: 2,
          constPersonal:constPersonal,
        }
    });
  },

  //抄送人选择弹窗
  showcopyModal(){
    const { copyPersonal } = this.props.applyLoan;
    this.props.dispatch({
        type: 'applyLoan/modelHandle',
        payload: {
          isshowcopymodal:true,
          modalIndex: 3,
          copyPersonal:copyPersonal,
        }
    });
  },
  //提交报销
  showsubmitModal(){
    const {
        getFieldDecorator,
        validateFields,
        getFieldsValue,
        getFieldValue
    } = this.props.form;

    const CardDetail = { ...getFieldsValue() };
    const {constdata,copydata} = this.props.applyLoan;
    validateFields((errors) => {
            if (errors) {
                return;
            }else{
                if( CardDetail.money != undefined && CardDetail.code != undefined && CardDetail.des != undefined && constdata.length > 0 ){
                    this.props.dispatch({
                        type: 'applyLoan/ApplyIDquery',
                        payload: {
                          issubmitmodal:true,
                          modalIndex: 0,
                          CardDetail:CardDetail,
                          bank_name:CardDetail.code.split(" ")[0],
                          bank_id:CardDetail.code.split(" ")[1],
                          type:2,
                        }
                    });
                }else{
                  message.error("请填写完必选项再提交!");
                }
            }
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
              urltype:2
          }
      });
  },
  beforeImgUpload(file,fileList) {
    const size = 20971520;
    const fileName = file.name;
    const type = fileName.substring(fileName.lastIndexOf('.') + 1).toLowerCase();
    const types = acceptImgFormat.toString().split(',');
    if (types.length > 0) {
      let b = false;
      for (let i = 0; i < types.length; i++) {
        if (types[i] === type) {
          b = true;
          break;
        }
      }
      if (b === false) {
        message.error(`上传文件格式错误!只支持[${acceptImgFormat}]`,4);
        return false;
      }

      if (file.size > size) {
        message.error('上传图片大小超过限制!最多20M');
        return false;
      }
      return true;
    }
    message.error('上传文件格式错误!');
    return false;
  },
  handlefileChange(info) {
    let fileList = info.fileList;
    this.setState({
        fileList:fileList
    });
  },
  handleimgChange(info){
    this.setState({
        imgfileList:info.fileList
    });
  },
  handlePreview(info){
    this.setState({
      previewImage: info.url || info.thumbUrl,
      previewVisible: true,
    });
  },
  handleCancel(){
    this.setState({
      previewVisible: false
    });
  },
  btnhandleCancel(){
      this.props.form.resetFields();
      const {constdata,copydata} = this.props.applyLoan;
      constdata.length = 0;
      copydata.length = 0;
      this.props.dispatch({
          type:'applyLoan/modelHandle',
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

    const {isshowcardmodal,isshowconstmodal,isshowcopymodal,issubmitmodal,constCard,constdata,copydata} = this.props.applyLoan;

    //const GenTable = () => <ApplyTable tabledata={tabledata}/>;

    const GenAddCardModal = () => <AddCardModal isshowcardmodal = {isshowcardmodal} />;
    const GenAddConstModal = () => <AddConstModal key={constmodalProps} title="审批人" isshowconstmodal = {isshowconstmodal}/>


    const imguploadprops = {
      action:'',
      name: 'avatar',
      listType:"picture-card",
      fileList: this.state.imgfileList,
      onChange: this.handleimgChange,
    };

    const  cardOptions = constCard.map(data =><Option key={cardmodalProps} value={data.bank_name+" "+data.card_id+" "+data.bank_des}>{data.bank_name+"-"+data.card_id}</Option>);

    const { previewVisible, previewImage, imgfileList } = this.state;
    const uploadButton = (
                            <div>
                              <Icon type="plus" />
                              <div className="ant-upload-text">上传</div>
                            </div>
                        );

    //审批人
    const auditingLi =  constdata.map(function(data,index) {
                              return (
                                  <AuditingLi key={index} dataid={index} id={data.id} imgvisiable={true} litype="1" name={data.name} />
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
    const GenconstPerson = () => <ApprovalPerson handleClick={this.showconstModal} approvalPerson={auditingLi} />;
    const GencopyPerson = () => <ApprovalPerson handleClick={this.showcopyModal} approvalPerson={copyLi} />;

    return (
      <Main location={location}>
        <Row>
          <GenAddCardModal />
          <GenAddConstModal />
          <AddCopyModal key={copymodalProps} title="抄送人" isshowcopymodal = {isshowcopymodal} />
          <SubmitModal key={submitmodalProps} issubmitmodal = {issubmitmodal} handleSubmit={this.handleSubmit} />

          <Form>
            <Pagetitle isback='true' title = '借款申请表'/>
            <FormItem {...formItemLayout} label="借款金额">
                {getFieldDecorator('money', {
                    validateTrigger:['onBlur','onFocus'],
                    valuePropName:'value',
                    rules: [
                      { required: true, message: '请输入借款金额!'},
                      { pattern:/(^[1-9]([0-9]+)?(\.[0-9]{1,2})?$)|(^(0){1}$)|(^[0-9]\.[0-9]([0-9])?$)/,message:'金额格式错误!'},
                      { max:13, message:'金额格式错误!'}
                    ]
                })(
                    <Input placeholder="请输入" />
                )}
            </FormItem>
            <FormItem {...formItemLayout} label="借款到">
                {getFieldDecorator('code', {
                    rules: [{ required: true, message: '请选择银行卡!'}]
                })(
                    <Select  className="f-l" showSearch style={{ width: '60%' }} placeholder="请选择">
                        {cardOptions}
                    </Select>
                )}
                <div className={cs('ml-md','ant-col-sm-7')}><a href="javascript:;" onClick={this.showcardModal}>需要借款到其他银行卡？</a></div>
            </FormItem>
            <FormItem {...formItemLayout} label="事由">
                {getFieldDecorator('des',{
                    rules: [
                      { required: true, message: '请输入事由!'},
                      { max:600, message:'输入字数超过最大限制!'}
                    ]
                })(
                   <Input placeholder="请输入事由" type="textarea" rows="4" />
                )}
            </FormItem>
            <FormItem {...formItemLayout} label="备注">
                {getFieldDecorator('tips',{
                  rules: [
                      { max:600, message:'输入字数超过最大限制!'}
                  ]
                })(
                   <Input placeholder="请输入备注" type="textarea" rows="4" />
                )}
            </FormItem>
            <FormItem {...formItemLayout} label="上传图片">
                {getFieldDecorator('pics')(
                  <Upload
                    action="/oa_v1/upload/image"
                    name="pics"
                    multiple={true}
                    listType="picture-card"
                    supportServerRender={true}
                    accept='jpg,png,gif'
                    fileList={imgfileList}
                    beforeUpload={this.beforeImgUpload}
                    onPreview={this.handlePreview}
                    onChange={this.handleimgChange}
                  >
                    {imgfileList.length >= 7 ? null : uploadButton}
                  </Upload>
                )}
                <Modal visible={previewVisible} footer={null} onCancel={this.handleCancel}>
                    <img alt="example" style={{ width: '100%' }} src={previewImage} />
                </Modal>
            </FormItem>
            {/*<h3 className={cs("mt-md","mb-md")}>审批人和抄送人</h3>
            <FormItem>
                <span className={cs('ant-col-md-2','ant-col-sm-3','t-r')}><label className="labelt">审批人： </label></span>
                <div className={styles.approval_wrap} >
                      <ul>
                        {auditingLi}
                        <li className={styles.add_approval} onClick={this.showconstModal}>
                            <Icon type="plus" />
                        </li>
                      </ul>
                </div>
            </FormItem>
            <FormItem>
            <span className={cs('ant-col-md-2','ant-col-sm-3','t-r')}><label>抄送人： </label></span>
                <div className={styles.approval_wrap} >
                      <ul>
                        {copyLi}
                        <li className={styles.add_approval} onClick={this.showcopyModal}>
                            <Icon type="plus" />
                        </li>
                      </ul>
                </div>
            </FormItem>*/}
            <h3 className={cs("mt-md","mb-lg")}>审批人和抄送人</h3>
            <FormItem {...formItemLayout} label="审批人" className="labelt">
                <GenconstPerson />
            </FormItem>
            <FormItem {...formItemLayout} label="抄送人" >
                <GencopyPerson />
            </FormItem>
            <FormItem>
                   <Button className={cs('mt-md','mb-md','ant-col-sm-offset-2')} type="primary" onClick={this.showsubmitModal}>确定</Button>
                   <Button className={cs('mt-md','mb-md','ml-md')} onClick={this.btnhandleCancel} >取消</Button>
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


ApplyLoan.propTypes = {
   location: PropTypes.object,
   dispatch: PropTypes.func,
   applyLoan: PropTypes.object
};

function mapStateToProps({ applyLoan }) {
  return { applyLoan };
}

export default connect(mapStateToProps)(Form.create()(ApplyLoan));
