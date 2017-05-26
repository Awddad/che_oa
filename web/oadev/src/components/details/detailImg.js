//详情图片
import React,{ Component,PropTypes} from 'react';
import { routerRedux } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Button,Select, Row, Col,message, Steps,Popover,Table,Modal } from 'antd';
import { key,host } from '../../components/common';
import styles from '../../routes/style.less';
import cs from 'classnames';
const Option = Select.Option;
const FormItem = Form.Item;

const DetailImg = React.createClass({
    getInitialState(){
      return {
        previewVisible: false,
        previewImage:''
      }
    },
    handleimgclick(event){
        let src =  event.target.parentNode.firstChild.src != null ? event.target.parentNode.firstChild.src : event.target.parentNode.parentNode.firstChild.src;
        this.setState({
            previewVisible:true,
            previewImage:src
        })
    },
    handleCancel(){
        this.setState({
            previewVisible:false,
        })
    },
    render(){
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

        const imgdata = this.props.imgdata || [];
        let imgli = '';
        if(imgdata.length > 0){
            imgli = imgdata.map(data =>
                data.length >0 ?
                (<li key={key(500000)} style={{marginRight:10}}>
                    <img width="100" height="120" src={host + data} />
                    <a href="javascript:;" onClick={this.handleimgclick}><Icon type="eye-o" /></a>
                </li>)
                :
                '--'
            );
        }else{
            imgli = '--';
        }

        return(
            <FormItem {...formItemLayout}  label="图片">
                <ul className={styles.listimgbox}>
                    {imgli}
                </ul>
                <Modal visible={this.state.previewVisible} footer={null} onCancel={this.handleCancel}>
                    <img alt="example" style={{ width: '100%' }} src={this.state.previewImage} />
                </Modal>
            </FormItem>
        );
    }
})

DetailImg.propTypes = {
   location: PropTypes.object,
   dispatch: PropTypes.func,
};

export default DetailImg;