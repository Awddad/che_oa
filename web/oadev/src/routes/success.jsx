import React,{ Component,PropTypes} from 'react';
import { connect } from 'dva';
import {Form,Icon,Button,Steps,message,Row,Col } from 'antd';
import { routerRedux } from 'dva/router';
import cs from 'classnames';
import Main from '../components/home/main';
import styles from './success.less';
const Step = Steps.Step;

const Success = React.createClass({
    handleListClick(event){
        dispatch(routerRedux.push({ pathname: 'mysend' }));
    },
    handleDetailClick(){
        let url = '';
        this.props.dispatch(
            routerRedux.push({
                pathname: ''
            })
        );
    },
    render(){
        let { Detail } = this.props.success;
        Detail = Detail == null ? {}:Detail;
        console.log(Detail);
        let title=null,applyid=null,copy_person=null,time=null,resultSteps=null,step=null;
        if(Object.keys(Detail).length > 0)
        {
            title = Detail.title;
            applyid = Detail.apply_id;
            time = Detail.create_time;
            copy_person=Detail.copy_person.map(data => data.person).join("、");
            step = Detail.approval.map(data => <Step key={Math.floor(Math.random()*100000)} title={data.person} description={ data.des } /> );
            resultSteps =   (<Steps current={1} progressDot>
                                <Step title="发起申请" key="00001" description={ Detail.person +" "+ Detail.create_time +" 南京汽车销售-中规车一区-南京店" } />
                                {step}
                                <Step title="完成" key="10000" description="" />
                            </Steps>);
        }
        return(
            <Main location={location}>
                <div className={styles.success_wrap}>
                    <div className="t-c">
                        <Icon type="check-circle" className={cs('t-c','mb-lg')} style={{fontSize:64,color:'#00A854', marginTop:50}}/>
                    </div>
                    <h1 className={cs('t-c','mb-sm')} style={{marginBottom:8}}>申请成功</h1>
                    <p className={cs('t-c','mb-lg')} >您的申请已提交成功，请耐心等待审批人审批，您可以点击“进入列表”到我发起的列表中查看审批进度，也可以点击“查看项目”，进入项目详情查看申请情况。</p>
                    <div className={styles.stepbox}>
                        <h3 className="mb-md">{title}</h3>
                        <Row className="mb-lg">
                            <Col span='8'>发起时间：{ time }</Col>
                            <Col span='8'>审批单编号：{ applyid }</Col>
                            <Col span='8'>抄送人：{ copy_person }</Col>
                        </Row>
                        { resultSteps }
                    </div>
                    <div className={cs('t-c','mt-md')}>
                        <Button type="primary" className="mr-md" size="large" onClick={this.handleListClick}>进入项目</Button>
                        <Button size="large" onClick={this.handleDetailClick} >查看项目</Button>
                    </div>
                </div>
            </Main>
        );
    }
});

Success.propTypes = {
   location: PropTypes.object,
   dispatch: PropTypes.func,
   success: PropTypes.object
}

function mapStateToProps({ success }) {
  return { success }
}

export default connect(mapStateToProps)(Success);