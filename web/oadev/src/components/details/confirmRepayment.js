//还款确认
import React,{ Component,PropTypes} from 'react';
import { routerRedux } from 'dva/router';
import { connect } from 'dva';
import { Form, Button ,Icon,Modal,Select,Input,Cascader,DatePicker,Radio,Upload } from 'antd';
import styles from '../../routes/style.less';
import cs from 'classnames';
const Option = Select.Option;
const FormItem = Form.Item;
const RadioGroup = Radio.Group;

const Confirm = React.createClass({
    getInitialState(){
        return {
            ...this.props.Detail,
            imgfileList:[],
            previewVisible: false,
            previewImage: '',
            flowvalue:1,
            repaymentTime:null
        };
    },
    handleflowChange(e){
        this.setState({
            flowvalue:e.target.value
        })
    },
    timeChange(date,dateString) {
        this.setState({
            repaymentTime:Date.parse(new Date(date))/1000
        })
    },
    handleSubmit(){
        const {
            getFieldDecorator,
            validateFields,
            getFieldsValue,
            getFieldValue
        } = this.props.form;
        const repaymentDetail = { ...getFieldsValue() };
        const { repaymentDepartmentData,repaymentaccountData,ApplyID } = this.props.Detail;
        validateFields((errors) => {
            if (errors) {
                return;
            }else{
                this.props.dispatch({
                    type: 'Detail/RepayMentConfirm',
                    payload: {
                        apply_id:ApplyID,
                        org_id:repaymentDetail.department.key,
                        bank_card_id:(repaymentDetail.account.key).split(" ")[1],
                        bank_name:(repaymentDetail.account.key).split(" ")[0],
                        shou_kuan_id:repaymentDetail.flowaccount,
                        create_cai_wu_log:repaymentDetail.flow,
                        shou_kuan_time:this.state.repaymentTime,
                        type:repaymentDetail.repaymentType[repaymentDetail.repaymentType.length-1],
                        pics:repaymentDetail.pics,
                        tips:repaymentDetail.tips
                    }
                });
            }
        });
    },
    onCancel(){
        this.props.dispatch({
            type:'Detail/hideModal',
            payload:{
                isShowRepaymentConfirm:false
            }
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
        const { repaymentDepartmentData,repaymentaccountData,repaymentType,repaymentFlowaccount,repaymentTime } = this.props.Detail;
        const { getFieldDecorator } = this.props.form;
        const { previewVisible, previewImage, imgfileList } = this.state;

        const modalOpts = {
          visible:this.props.isShowRepaymentConfirm,
          onOk: this.handleSubmit,
          onCancel: this.onCancel,
        };

        const formItemLayout = {
          labelCol: {
            xs: { span: 24 },
            sm: { span: 6 }
          },
          wrapperCol: {
            xs: { span: 24 },
            sm: { span: 16 }
          }
        };

        let departmentOptions=[],accountOptions=[],TypeOptions=[];
        if(Object.keys(repaymentDepartmentData).length > 0 || repaymentDepartmentData != undefined){
            for(var key of Object.keys(repaymentDepartmentData)){
                departmentOptions.push(<Option key={key}>{ repaymentDepartmentData[key] }</Option>);
            }
        }
        if(repaymentaccountData.length > 0){
            accountOptions = repaymentaccountData.map(data => <Option key={data.name+' '+data.number}>{data.name_short}</Option>);
            TypeOptions = TypeOptions = repaymentType;
        }

        const uploadButton = (
                            <div>
                              <Icon type="plus" />
                              <div className="ant-upload-text">上传</div>
                            </div>
                        );
        const imguploadprops = {
          action:'',
          name: 'avatar',
          listType:"picture-card",
          fileList: this.state.imgfileList,
          onChange: this.handleimgChange,
        };

        const details = this.props.details || {};
        let name=null,bank_name=null,bank_id=null,bank_des=null;
        if(Object.keys(details).length > 0){
            name = details.person
            bank_id = details.info.bank_card_id;
            bank_name = details.info.bank_name;
            bank_des = details.info.bank_des;
        }
        return(
                <Modal title="收款确认"  {...modalOpts} >
                        <Form>
                            <FormItem {...formItemLayout}  label="打款人">
                                <p style={{marginTop:5}}>{ name }&nbsp;&nbsp;&nbsp;{ bank_name }</p>
                                <p>{ bank_id }</p>
                                <p>{ bank_des }</p>
                            </FormItem>
                            <FormItem {...formItemLayout} label="收款部门" hasFeedback>
                                {getFieldDecorator('department', {
                                    rules: [{required: true, message: '请选择收款部门',}]
                                })(
                                    <Select className="t-l" labelInValue  placeholder="请选择" size="large" style={{ width: '100%' }}>
                                          {departmentOptions}
                                    </Select>
                                )}
                            </FormItem>
                            <FormItem {...formItemLayout} label="收款账号" hasFeedback>
                                {getFieldDecorator('account', {
                                    rules: [{required: true, message: '请选择收款账号',}]
                                })(
                                    <Select className="t-l" labelInValue  placeholder="请选择" size="large" style={{ width: '100%' }}>
                                          {accountOptions}
                                    </Select>
                                )}
                            </FormItem>
                            <FormItem {...formItemLayout} label="收款类型" hasFeedback>
                                {getFieldDecorator('repaymentType', {
                                    rules: [{required: true, message: '请选择收款类型',}]
                                })(
                                    <Cascader
                                        options={TypeOptions}
                                        placeholder="请选择"
                                        showSearch
                                    />
                                )}
                            </FormItem>
                            <FormItem {...formItemLayout} label="流水号" hasFeedback>
                                {getFieldDecorator('flowaccount', {
                                    rules: [{required: true, message: '请输入流水号',}]
                                })(
                                    <Input type="text" placeholder="请输入" />
                                )}
                            </FormItem>
                            <FormItem {...formItemLayout} label="收款时间" hasFeedback>
                                {getFieldDecorator('repaymentTime', {
                                    rules: [{required: true, message: '请选择收款时间',}]
                                    })(
                                    <DatePicker showTime format="YYYY-MM-DD HH:mm" placeholder="请选择时间" onChange={this.timeChange} />
                                )}
                            </FormItem>
                            <FormItem {...formItemLayout} label="备注">
                                {getFieldDecorator('tips')(
                                    <Input type="textarea" placeholder="请输入备注" />
                                )}
                            </FormItem>
                            <FormItem {...formItemLayout} label="财务流水" hasFeedback>
                                {getFieldDecorator('flow', {
                                    rules: [{required: true, message: '请选择财务流水',}]
                                    })(
                                    <RadioGroup onChange={this.handleflowChange} value={this.state.flowvalue}>
                                        <Radio value={1}>自动生成</Radio>
                                        <Radio value={0}>不生成</Radio>
                                    </RadioGroup>
                                )}
                            </FormItem>
                            <FormItem {...formItemLayout} label="上传凭证">
                                {getFieldDecorator('pics')(
                                  <Upload {...imguploadprops}>
                                    {imgfileList.length >= 3 ? null : uploadButton}
                                  </Upload>
                                )}
                                  <Modal visible={previewVisible} footer={null} onCancel={this.handleCancel}>
                                    <img alt="example" style={{ width: '100%' }} src={previewImage} />
                                  </Modal>
                            </FormItem>
                        </Form>
                </Modal>
        );
    }
})

Confirm.propTypes = {
   location: PropTypes.object,
   dispatch: PropTypes.func,
};

function mapStateToProps({ Detail }) {
  return { Detail };
}

export default connect(mapStateToProps)(Form.create()(Confirm));