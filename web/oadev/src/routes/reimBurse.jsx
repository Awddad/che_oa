import { connect } from 'dva';
import React,{ Component,PropTypes} from 'react';
import {Form,Icon,Button,Input,Checkbox,Select,Upload,Row,Col,Modal,message } from 'antd';
import Pagetitle from '../components/public/pagetitle';
import ApplyTable from '../components/reimburse/table';
import AuditingLi from '../components/reimburse/auditingLi';
import AddTableModal from '../components/reimburse/addTableModal';
import AddCardModal from '../components/reimburse/addCardModal';
import AddConstModal from '../components/reimburse/addConstModal';
import AddCopyModal from '../components/reimburse/addCopyModal';
import SubmitModal from '../components/reimburse/submitModal';
import {Bread} from '../components/common';
import Main from '../components/home/main';
import { routerRedux } from 'dva/router';
import styles from './reimburse.less';
import cs from 'classnames';
const Option = Select.Option;
const FormItem = Form.Item;
import WebStorage from 'react-webstorage';
const webStorage = new WebStorage(window.localStorage || window.sessionStorage);


const Reimburse = React.createClass({
  getInitialState(){
    //Bread("报销管理","OneCrumb");
    Bread("申请报销","OneCrumb");
    return {
      ...this.props.reimBurse,
      fileList: [],
      imgfileList:[],
      previewVisible: false,
      previewImage: '',
    };
  },
  componentWillMount() {
    /*const itemFileList = [];
    const item = this.props.item;
    // 图片上传
    if (item.imgUrl) {
      itemFileList.push({
        uid: -1,
        status: 'done',
        url: item.imgUrl
      });
    }
    this.setState({
      fileList: itemFileList,
      checked: item.urlTarget === 1 || false
    });*/
  },
  componentDidMount() {

  },
  //表格添加弹窗
  showtableModal(){
    this.props.dispatch({
        type: 'reimBurse/modelHandle',
        payload: {
          isshowtablemodal:true,
          modalIndex: 1
        }
    });
  },

    //银行卡添加弹窗
  showcardModal(){
    this.props.dispatch({
        type: 'reimBurse/modelHandle',
        payload: {
          isshowcardmodal:true,
          modalIndex: 0
        }
    });
  },

  //审批人选择弹窗
  showconstModal(){
    const { constPersonal,copyPersonal } = this.props.reimBurse;
    this.props.dispatch({
        type: 'reimBurse/modelHandle',
        payload: {
          isshowconstmodal:true,
          modalIndex: 2,
          constPersonal:constPersonal,
        }
    });
  },

  //抄送人选择弹窗
  showcopyModal(){
    const { copyPersonal,constPersonal } = this.props.reimBurse;
    this.props.dispatch({
        type: 'reimBurse/modelHandle',
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
    const { tabledata,constdata,copydata } = this.props.reimBurse;
    validateFields((errors) => {
            if (errors) {
                return;
            }else{
                if(tabledata.length >0 && CardDetail.code != undefined && constdata.length > 0 ){
                    this.props.dispatch({
                        type: 'reimBurse/ApplyIDquery',
                        payload: {
                          issubmitmodal:true,
                          modalIndex: 0,
                          CardDetail:CardDetail,
                          fileList:CardDetail.file,
                          pics:CardDetail.pics,
                          bank_name:CardDetail.code.split(" ")[0],
                          bank_id:CardDetail.code.split(" ")[1],
                          type:1,
                        }
                    });
                }else{
                  message.error("请填写完必选项再提交!");
                }
            }
    });
  },
  handleSubmit(){
      let { tabledata,CardDetail,constdata,copydata,addApplyID } = this.props.reimBurse;

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
  beforeImgUpload(file,fileList) {
    const size = 20971520;//20M
    const fileName = file.name;
    const type = fileName.substring(fileName.lastIndexOf('.') + 1).toLowerCase();
    const acceptImgFormat = 'jpg,png,gif';   //上传图片格式
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
  beforeFileUpload(file,fileList) {
    const size = 20971520; //20M
    const fileName = file.name;
    const type = fileName.substring(fileName.lastIndexOf('.') + 1).toLowerCase();
    const acceptFileFormat = 'doc,docx,pdf,xls,xlsx';   //上传文件格式
    const types = acceptFileFormat.toString().split(',');
    if (types.length > 0) {
      let b = false;
      for (let i = 0; i < types.length; i++) {
        if (types[i] === type) {
          b = true;
          break;
        }
      }
      if (b === false) {
        message.error(`上传文件格式错误!只支持[${acceptFileFormat}]`,4);
        return false;
      }

      if (file.size > size) {
        message.error('上传文件大小超过限制!最多20M');
        return false;
      }
      return true;
    }
    message.error('上传文件格式错误!');
    return false;
  },
  handlefileChange(info) {
    let fileList = info.fileList;
    if(fileList.length > 7){
        message.error('上传文件数量已达上限!');
    }else{
        this.setState({
            fileList:fileList
        });
    }
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
      const {constdata,copydata,tabledata} = this.props.reimBurse;
      tabledata.length = 0;
      constdata.length = 0;
      copydata.length = 0;

      this.props.dispatch({
          type:'reimBurse/modelHandle',
          payload:{
              modalIndex:0,
              tabledata:tabledata,
              constdata:constdata,
              copydata:copydata
          }
      });

      webStorage.setItem("menuKey", "/adminhome");
      this.props.dispatch(routerRedux.push("/adminhome"));
  },
  render(){
    const tablemodalProps = Math.floor(Math.random()*100000);
    const cardmodalProps = Math.floor(Math.random()*200000);
    const constmodalProps = Math.floor(Math.random()*300000);
    const copymodalProps = Math.floor(Math.random()*400000);
    const submitmodalProps = Math.floor(Math.random()*500000);

    const {isshowtablemodal,isshowcardmodal,isshowconstmodal,isshowcopymodal,issubmitmodal,tabledata,constCard,constdata,copydata,bank} = this.props.reimBurse;

    const GenTable = () => <ApplyTable tabledata={tabledata}/>;

    const fileuploadprops = {
      action:'/oa_v1/upload/file',
      name: 'files',
      multiple: true,
      listType: 'file',
      fileList: this.state.fileList,
      beforeUpload:this.beforeFileUpload,
      onChange: this.handlefileChange,
    };

    const imguploadprops = {
      action:'',
      name: 'avatar',
      listType:"picture-card",
      fileList: this.state.imgfileList,
      onChange: this.handleimgChange,
    };

    const  cardOptions = constCard.map( data => <Option key={cardmodalProps} value={data.bank_name+" "+data.card_id +" "+data.bank_des}>{data.bank_name+'-'+data.card_id}</Option>);


    const { previewVisible, previewImage, imgfileList } = this.state;
    let auditingLi=null,copyLi=null;
    const uploadButton = (
                            <div>
                              <Icon type="plus" style={{fontSize:30}} />
                              <div className="ant-upload-text">上传</div>
                            </div>
                        );


    if( constdata.length > 0 ){
      //审批人
      auditingLi =  constdata.map(function(data,index) {
                                return (
                                    <AuditingLi key={index} dataid={index} id={data.id} imgvisiable={true} litype="1" name={data.name} />
                                );
                            });
    }
    if(copydata.length > 0 ){
      //抄送人
      copyLi =  copydata.map(function(data,index) {
                                return (
                                    <AuditingLi key={index} dataid={index} id={data.id} imgvisiable={false} litype="2" name={data.name} />
                                );
                            });
    }
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
          <AddTableModal key={tablemodalProps} isshowtablemodal = {isshowtablemodal} />
          <AddCardModal key={cardmodalProps} isshowcardmodal = {isshowcardmodal} />
          <AddConstModal key={constmodalProps} title="审批人" isshowconstmodal = {isshowconstmodal}/>
          <AddCopyModal key={copymodalProps} title="抄送人" isshowcopymodal = {isshowcopymodal} />
          <SubmitModal key={submitmodalProps} issubmitmodal = {issubmitmodal} handleSubmit={this.handleSubmit} />

          <Form>
            <Pagetitle title = '报销申请表'/>
            <FormItem {...formItemLayout} label="报销明细" className="labelt" >
               <GenTable/>
            </FormItem>
            <div className="clear">
              <Button className={cs('mb-lg','ant-col-sm-offset-2')} type="primary" onClick={this.showtableModal}>增加报销明细</Button>
            </div>
            <FormItem {...formItemLayout} label="报销到">
                {getFieldDecorator('code', {
                    rules: [{ required: true, message: '请选择银行卡!'}]
                })(
                        <Select className="f-l"
                          showSearch
                          style={{ width: '60%' }}
                          placeholder="请选择"
                        >
                          {cardOptions}
                        </Select>
                )}
                <a className={cs('ml-sm','ant-col-sm-7')} href="javascript:;" onClick={this.showcardModal}>需要报销到其他银行卡？</a>
            </FormItem>
            <FormItem {...formItemLayout} label="上传文件" >
                {getFieldDecorator('file')(
                <Upload className="f-l" {...fileuploadprops}>
                    <Button>
                        <Icon type="upload" /> 上传文件
                    </Button>
                </Upload>
                )}
                <span className={cs('f-l','ml-sm')}>报销差旅费和接待费请上传报销明细。</span>
                <a className={cs('f-l','ml-sm')} href="/template/车城体系财务模板.xlsx">模板下载</a>
            </FormItem>
            <FormItem {...formItemLayout} label="上传图片">
                {getFieldDecorator('pics')(
                  <Upload
                    action="/oa_v1/upload/image"
                    name="pics"
                    listType="picture-card"
                    multiple={true}
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
                  <p className={cs("clearfix")}>请拍照并上传发票等文件</p>
            </FormItem>

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
        return (
            <div className={styles.approval_wrap} >
                <ul>
                  {this.props.approvalPerson}
                  <li className={styles.add_approval} onClick={this.props.handleClick} >
                      <Icon type="plus" />
                  </li>
                 </ul>
            </div>
        )
    }
});


Reimburse.propTypes = {
   location: PropTypes.object,
   dispatch: PropTypes.func,
   reimBurse: PropTypes.object,
   applyIDuserInfo: PropTypes.object
};

function mapStateToProps({ reimBurse }) {
  return { reimBurse};
}

export default connect(mapStateToProps)(Form.create()(Reimburse));
