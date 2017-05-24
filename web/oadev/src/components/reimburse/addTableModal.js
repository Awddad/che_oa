import { connect } from 'dva';
import React,{ Component,PropTypes} from 'react';
import {Form,Icon,Button,Input,Checkbox,Select,Cascader,Upload,Row,Col,Modal } from 'antd';
import styles from '../../routes/reimburse.less';
import cs from 'classnames';
const FormItem = Form.Item;
const Option = Select.Option;

const AddTableModal = React.createClass({
    getInitialState(){
        return {
          ...this.props.reimBurse,
          text:''
        };
    },
    onOk(e){
        e.preventDefault();
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
            const tabledata = { ...getFieldsValue() };
            const { bxtypeID } = this.props.reimBurse;
            const row = {money:(tabledata.money*1).toFixed(2),type_name:this.state.text,des:tabledata.des,type:tabledata.bxType[tabledata.bxType.length-1]}
            this.props.dispatch({
              type: 'reimBurse/addtable',
              payload: {
                row:row,
                bxtypeID:tabledata.bxType[tabledata.bxType.length-1]
              }
            });
        });

    },
    onCancel(){
        this.props.dispatch({
            type: 'reimBurse/hideModal'
        });
    },
    handleChange(value, selectedOptions){
      this.setState({
        text: selectedOptions[selectedOptions.length-1].label,
      });
    },
    render(){
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
          visible:this.props.isshowtablemodal,
          onOk: this.onOk,
          onCancel: this.onCancel,
        };

        const { getFieldDecorator,getFieldsValue } = this.props.form;
        const {constType} = this.props.reimBurse;
        const typeOptions =constType;

        return(
                <Modal title="增加报销明细"  {...modalOpts} >
                        <Form>
                            <FormItem {...formItemLayout} label="报销金额" hasFeedback>
                                {getFieldDecorator('money', {
                                    rules: [
                                    {required: true, message: '请输入报销金额!'},
                                    { pattern:/(^[1-9]([0-9]+)?(\.[0-9]{1,2})?$)|(^(0){1}$)|(^[0-9]\.[0-9]([0-9])?$)/,message:'金额格式错误!'},
                                    ]
                                })(
                                    <Input placeholder="请输入金额" />
                                )}
                            </FormItem>
                            <FormItem
                              label="报销类别"
                              {...formItemLayout}
                              hasFeedback
                            >
                                {getFieldDecorator('bxType', {
                                    rules: [{ required: true, message: '请选择报销类别!' }]
                                })(
                                    /*<Select labelInValue  placeholder="请选择" size="large" style={{ width: '100%' }}>
                                        {typeOptions}
                                    </Select>*/
                                    <Cascader
                                      options={typeOptions}
                                      placeholder="请选择"
                                      onChange = {this.handleChange}
                                      showSearch
                                    />
                                )}
                            </FormItem>
                            <FormItem {...formItemLayout} label="费用明细" hasFeedback>
                                {getFieldDecorator('des')(
                                    <Input type="textarea" rows={4}  placeholder="请输入注备"/>
                                )}
                            </FormItem>
                        </Form>
                </Modal>
        );
    }
});

AddTableModal.propTypes = {
    location: PropTypes.object,
    form: PropTypes.object,
    onOk: PropTypes.func,
    onCancel: PropTypes.func,
    dispatch: PropTypes.func
}

function mapStateToProps({ reimBurse }) {
  return { reimBurse };
}

export default connect(mapStateToProps)(Form.create()(AddTableModal));