import { connect } from 'dva';
import _ from 'underscore';
import React,{ Component,PropTypes} from 'react';
import {Form,Icon,Button,Input,Checkbox,Select,Upload,Row,Col,Modal } from 'antd';
import styles from '../../routes/reimburse.less';
import cs from 'classnames';
const FormItem = Form.Item;
const Option = Select.Option;

const AddCopyModal = React.createClass({
  getInitialState(){
        return {
          index:null
        };
    },
    handleonOK(){
        const {
            getFieldDecorator,
            validateFields,
            getFieldsValue,
            getFieldValue
        } = this.props.form;

        validateFields((errors) => {
            if (errors) {
                return;
            }

            const copydata = { ...getFieldsValue() };
            const name = (copydata.audit_personal.label).split(" ")[0];
            const row = {'id':copydata.audit_personal.key,'name':name}
            const { copyPersonal } = this.props.reimBurse;
            this.props.dispatch({
              type: 'reimBurse/addcopy',
              payload: {
                row:row,
                type:1,
                index:this.state.index,
                copyPersonal:copyPersonal
              }
            });
        });
    },
    hanleSelect(value, option){
        this.setState({
            index:option.props.index
        });
    },
    onCancel(){
        this.props.dispatch({
            type: 'reimBurse/hideModal'
        });
    },
    render(){
        const formItemLayout = {
          labelCol: {
            xs: { span: 24 },
            sm: { span: 4 },
          },
          wrapperCol: {
            xs: { span: 24 },
            sm: { span: 18 },
          },
        };

        const modalOpts = {
          key:this.props.key,
          title:this.props.title,
          width: 600,
          visible:this.props.isshowcopymodal,
          onOk: this.handleonOK,
          onCancel: this.onCancel,
        };

        const { getFieldDecorator } = this.props.form;
        const {copyPersonal,copydata} = this.props.reimBurse;
        let personalOptions =[],constid = [];
        if(copydata.length>0){constid = copydata.map(data => parseInt(data.id));}
        if(copyPersonal != null){

            //personalOptions = copyPersonal.map(data => <Option key={data.id}>{data.name}</Option>);
            for(let i=0;i<copyPersonal.length;i++){
                if(copydata.length>0){
                    let personid=[];
                    personid.push(copyPersonal[i].id);
                    if(_.intersection(constid,personid).length>0){
                        personalOptions.push(<Option key={copyPersonal[i].id} disabled>{copyPersonal[i].name}</Option>);
                    }else{
                        personalOptions.push(<Option key={copyPersonal[i].id}>{copyPersonal[i].name}</Option>);
                    }
                }else{
                    personalOptions.push(<Option key={copyPersonal[i].id}>{copyPersonal[i].name}</Option>);
                }
            }
        }

        return(
                <Modal {...modalOpts} >
                        <Form>
                            <FormItem {...formItemLayout} label={this.props.title} hasFeedback>
                                    {getFieldDecorator('audit_personal', {
                                        rules: [{ required: true, message: '请选择抄送人!' }]
                                    })(
                                      <Select className="t-l" labelInValue  placeholder="请选择" size="large" style={{ width: '100%' }} onSelect = {this.hanleSelect}>
                                          {personalOptions}
                                      </Select>
                                    )}
                            </FormItem>
                        </Form>
                </Modal>
        );
    }
});

AddCopyModal.propTypes = {
    location: PropTypes.object,
    form: PropTypes.object,
    dispatch: PropTypes.func
}

function mapStateToProps({ reimBurse }) {
  return { reimBurse };
}

export default connect(mapStateToProps)(Form.create()(AddCopyModal));