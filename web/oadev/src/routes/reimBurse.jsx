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
import Main from '../components/home/main';
import styles from './reimburse.less';
import cs from 'classnames';
const Option = Select.Option;
const FormItem = Form.Item;



const acceptImgFormat = 'jpg,jpeg,png,gif,bmp';   //上传图片格式
const acceptFileFormat = 'doc,pdf,xls,xlsx,txt';   //上传图片格式

const Reimburse = React.createClass({
  getInitialState(){
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
    this.props.dispatch({
        type: 'reimBurse/modelHandle',
        payload: {
          isshowconstmodal:true,
          modalIndex: 2
        }
    });
  },

  //抄送人选择弹窗
  showcopyModal(){
    this.props.dispatch({
        type: 'reimBurse/modelHandle',
        payload: {
          isshowcopymodal:true,
          modalIndex: 2
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
                          fileList:CardDetail.fileList,
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

      this.props.dispatch({
          type: 'reimBurse/create',
          payload: {
              bank_card_id:(CardDetail.code).split(" ")[1],
              bank_name:(CardDetail.code).split(" ")[0],
              bank_name_des:(CardDetail.code).split(" ")[2],
              bao_xiao_list:tabledata,
              approval_persons:approval_persons,
              copy_person:copy_person,
              fujian:CardDetail.fileList,
              pics:CardDetail.pics,
              apply_id:addApplyID,
              urltype:1
          }
      });
  },
  beforeImgUpload(file,fileList) {
    const size = 2097152;
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
        message.error(`上传文件格式错误!只支持[${acceptImgFormat}]`);
        return false;
      }

      if (file.size > size) {
        message.error('上传图片大小超过限制!最多2M');
        return false;
      }
      return true;
    }
    message.error('上传文件格式错误!');
    return false;
  },
  beforeFileUpload(file,fileList) {
    const size = 5242880;
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
        message.error(`上传文件格式错误!只支持[${acceptImgFormat}]`);
        return false;
      }

      if (file.size > size) {
        message.error('上传文件大小超过限制!最多5M');
        return false;
      }
      return true;
    }
    message.error('上传文件格式错误!');
    return false;
  },
  handlefileChange(info) {
    let fileList = info.fileList;
    /*const file = info.file;
    const status = info.file.status;
    const resp = info.file.response;
    console.log(status);
    if (status === 'done') {
      if (resp && resp.statusCode !== 1) {
        message.error('上传失败');
        fileList = fileList.filter((f) => {
          const thisFileResp = f.response;
          return thisFileResp.content && thisFileResp.statusCode === 1;
        });
      }
    }*/

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
  render(){
    const tablemodalProps = Math.floor(Math.random()*100000);
    const cardmodalProps = Math.floor(Math.random()*200000);
    const constmodalProps = Math.floor(Math.random()*300000);
    const copymodalProps = Math.floor(Math.random()*400000);
    const submitmodalProps = Math.floor(Math.random()*500000);

    const {isshowtablemodal,isshowcardmodal,isshowconstmodal,isshowcopymodal,issubmitmodal,tabledata,constCard,constdata,copydata} = this.props.reimBurse;

    const GenTable = () => <ApplyTable tabledata={tabledata}/>;

    const fileuploadprops = {
      action:'',
      name: 'file',
      multiple: true,
      listType: 'file',
      fileList: this.state.fileList,
      onChange: this.handlefileChange,
    };

    const imguploadprops = {
      action:'',
      name: 'avatar',
      listType:"picture-card",
      fileList: this.state.imgfileList,
      onChange: this.handleimgChange,
    };

    const  cardOptions = constCard.map(data =><Option key={tablemodalProps} value={data.bank_name+" "+data.card_id +" "+data.bank_des}>{data.bank_name+'-'+data.card_id}</Option>);

    const { previewVisible, previewImage, imgfileList } = this.state;
    const uploadButton = (
                            <div>
                              <Icon type="plus" />
                              <div className="ant-upload-text">上传</div>
                            </div>
                        );

    //审核人
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
            sm: { span: 3 },
            md: { span: 2 },
          },
          wrapperCol: {
            xs: { span: 24 },
            sm: { span: 14 },
          },
        };

    //console.log(tabledata);
    const { getFieldDecorator } = this.props.form;

    return (
      <Main location={location}>
        <Row>
          <AddTableModal key={tablemodalProps} isshowtablemodal = {isshowtablemodal} />
          <AddCardModal key={cardmodalProps} isshowcardmodal = {isshowcardmodal} />
          <AddConstModal key={constmodalProps} title="审核人" isshowconstmodal = {isshowconstmodal}/>
          <AddCopyModal key={copymodalProps} title="抄送人" isshowcopymodal = {isshowcopymodal} />
          <SubmitModal key={submitmodalProps} issubmitmodal = {issubmitmodal} handleSubmit={this.handleSubmit} />

          <Form>
            <Pagetitle isback='true' title = '申请报销'/>
            <h3 className={cs("mt-md","mb-md")}>报销申请表</h3>
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
                {getFieldDecorator('fileList')(
                <Upload className="f-l" {...fileuploadprops}>
                    <Button>
                        <Icon type="upload" /> 上传文件
                    </Button>
                </Upload>
                )}
                <span className={cs('f-l','ml-sm')}>报销差旅费和接待费请上传报销明细。</span>
                <a className={cs('f-l','ml-sm')} href="#">模板下载</a>
            </FormItem>
            <FormItem {...formItemLayout} label="上传图片">
                {getFieldDecorator('pics')(
                  <Upload
                    action=""
                    name="avatar"
                    listType="picture-card"
                    fileList={imgfileList}
                    onPreview={this.handlePreview}
                    onChange={this.handleimgChange}
                  >
                    {imgfileList.length >= 5 ? null : uploadButton}
                  </Upload>
                )}
                  <Modal visible={previewVisible} footer={null} onCancel={this.handleCancel}>
                    <img alt="example" style={{ width: '100%' }} src={previewImage} />
                  </Modal>
                 <p className={cs("clear")}>请拍照并上传发票等文件</p>
            </FormItem>
            <h3 className={cs("mt-md","mb-lg")}>审批人和抄送人</h3>
            <FormItem {...formItemLayout} label="审批人" className="labelt">
                <div className={styles.approval_wrap} >
                      <ul>
                        {auditingLi}
                        <li className={styles.add_approval} onClick={this.showconstModal}>
                            <Icon type="plus" />
                        </li>
                      </ul>
                </div>
            </FormItem>
            <FormItem {...formItemLayout} label="抄送人" >
                <div className={styles.approval_wrap} >
                      <ul>
                        {copyLi}
                        <li className={styles.add_approval} onClick={this.showcopyModal}>
                            <Icon type="plus" />
                        </li>
                      </ul>
                </div>
            </FormItem>
            <FormItem>
                   <Button className={cs('mt-md','mb-md','ant-col-sm-offset-2')} type="primary" onClick={this.showsubmitModal}>确定</Button>
                   <Button className={cs('mt-md','mb-md','ml-md')} >取消</Button>
            </FormItem>
          </Form>
        </Row>
      </Main>
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
