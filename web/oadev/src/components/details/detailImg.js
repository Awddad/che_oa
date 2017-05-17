//详情图片
import React,{ Component,PropTypes} from 'react';
import { routerRedux } from 'dva/router';
import { connect } from 'dva';
import { Form, Icon, Button,Select, Row, Col,message, Steps,Popover,Table } from 'antd';

import cs from 'classnames';
const Option = Select.Option;
const FormItem = Form.Item;

const DetailImg = React.createClass({
    render(){
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

        const imgdata = this.props.imgdata || [];
        let imgli = '';
        if(imgdata.length > 0){
            imgli = imgdata.map(data => (<li><img src={data.url} alt={data.alt} /></li>));
        }
        return(
            <FormItem {...formItemLayout}  label="图片">
                <ul>
                    {imgli}
                </ul>
            </FormItem>
        );
    }
})

DetailImg.propTypes = {
   location: PropTypes.object,
   dispatch: PropTypes.func,
};

export default DetailImg;