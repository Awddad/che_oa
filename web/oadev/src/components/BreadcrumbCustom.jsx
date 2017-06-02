import React from 'react';
import { Breadcrumb, Switch, Icon } from 'antd';
import { Link } from 'react-router';

class BreadcrumbCustom extends React.Component {
    render() {
        const ftz =  this.props.furl == "" ? (<span>{this.props.first}</span>) : (<Link to={this.props.furl}>{this.props.first}</Link>);
        const first = <Breadcrumb.Item>{ftz}</Breadcrumb.Item> || '';
        const second = <Breadcrumb.Item>{this.props.second}</Breadcrumb.Item> || '';
        return (
            <span>
                <Breadcrumb style={{ margin: '12px 0' }}>
                    <Breadcrumb.Item><Link to={'/adminhome'}>首页</Link></Breadcrumb.Item>
                    {first}
                    {second}
                </Breadcrumb>
            </span>
        );
    }
}

export default BreadcrumbCustom;
