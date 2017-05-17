//步骤
import React,{ Component,PropTypes} from 'react';
import { routerRedux } from 'dva/router';
import { connect } from 'dva';
import { Icon, Button, Row, Col,message,Steps} from 'antd';
import Pagetitle from '../public/pagetitle';
import styles from '../../routes/style.less';
import cs from 'classnames';
const Step = Steps.Step;

const StepDetails = React.createClass({
    getInitialState(){
        return {

        };
    },
    render(){
        const stepdata = this.props.stepdata;
        let step = '',resultSteps = '';
        let name='',copypersonal='',createTime='',des='',applyID='';
        if(Object.keys(stepdata).length > 0){
            step = stepdata.approval.map(data => <Step key={Math.floor(Math.random()*100000)} title={data.person} description={ data.des } /> );
            resultSteps =   (<Steps current={1} progressDot>
                                <Step title="发起申请" key="00001" description={ stepdata.person +" "+ stepdata.create_time +" 南京汽车销售-中规车一区-南京店" } />
                                {step}
                                <Step title="完成" key="10000" description="" />
                            </Steps>);

            name = stepdata.person;
            copypersonal = stepdata.copy_person.map(data => data.person).join("、");
            createTime = stepdata.create_time;
            des = stepdata.next_des;
            applyID = stepdata.apply_id;
        }

        return(
            <div>
                <div className={styles.loan_box}>
                    <div className={styles.img}>
                        <img src={require('../../assets/logo.png')} style={{marginTop:5}} />
                    </div>
                    <div className={styles.load_tit}>
                        <h2>{stepdata.title}</h2>
                        <Row>
                            <Col xs={{ span: 5}} lg={{ span: 8}}>发起人：{ name }</Col>
                            <Col xs={{ span: 11}} lg={{ span: 8}}>抄送人：{ copypersonal }</Col>
                            <Col xs={{ span: 5}} lg={{ span: 8}}></Col>
                        </Row>
                        <Row>
                            <Col xs={{ span: 5}} lg={{ span: 8}}>发起时间：{ createTime }</Col>
                            <Col xs={{ span: 11}} lg={{ span: 8}}>当前状态：{ des }</Col>
                            <Col xs={{ span: 5}} lg={{ span: 8}}>审批单编号：{ applyID }</Col>
                        </Row>
                    </div>
                    <div className="clearfix"></div>
                </div>
                <h2 className={cs('mt-lg','mb-md')}>审批进度</h2>
                {resultSteps}
            </div>
        );
    }
});

StepDetails.propTypes = {
   location: PropTypes.object,
   dispatch: PropTypes.func,
};

export default StepDetails;