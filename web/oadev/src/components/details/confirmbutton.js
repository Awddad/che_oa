import React,{ Component,PropTypes} from 'react';
import { Button} from 'antd';
import cs from 'classnames';

const ConfirmButton = React.createClass({
    render(){
        return (
            <Button className={cs('ant-col-md-offset-2','ant-col-sm-offset-3','mb-lg')} size="large" type="primary" onClick={this.props.handleClick} >确认</Button>
        );
    }
});

ConfirmButton.propTypes = {
   location: PropTypes.object,
   Detail: PropTypes.object,
   dispatch: PropTypes.func,
};

export default ConfirmButton;