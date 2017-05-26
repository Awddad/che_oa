//步骤
import React,{ Component,PropTypes} from 'react';
import { routerRedux } from 'dva/router';
import { connect } from 'dva';
import { Icon, Button, Row, Col,message,Steps} from 'antd';
import Pagetitle from '../public/pagetitle';
import styles from '../../routes/style.less';
import { deff_time } from '../common';
import cs from 'classnames';
const Step = Steps.Step;


const StepDetails = React.createClass({
    getInitialState(){
        return {

        };
    },
    Des(a,b,c){

    },
    render(){
        const stepdata = this.props.stepdata;
        let step = '',resultSteps = '';
        let name='',copypersonal='',createTime='',des='',applyID='';

        if(Object.keys(stepdata).length > 0){
            let status = stepdata.status;
            step = stepdata.flow.map(data =>
                <Step key={Math.floor(Math.random()*1000000)} title={
                        data.status == 1 && status == 3 || data.status ==3 && status == 4
                        ?
                            (<div className="cred">{data.title}</div>)
                        :
                            (<div>{data.title}</div>)
                    }
                    description={
                        status != 3?
                            (<div>
                                <div>{data.name+ "  " + data.date}</div>
                                <div>{data.org}</div>
                                <div>{
                                    data.status == '1' ?
                                    data.diff_time==0 ? '':"已等待："+ deff_time(data.diff_time)
                                    :
                                    data.diff_time==0 ? '':"耗费："+ deff_time(data.diff_time)
                                }</div>
                                <div>{ data.des==''?'':"说明："+ data.des}</div>
                            </div>)
                        :
                                    data.status != 1 ?
                                        (<div><div>{data.name+ "  " + data.date}</div>
                                            <div>{data.org}</div></div>)
                                    :
                                        (<div><div>{data.name+ "  " + data.date}</div>
                                        <div>{data.org}</div>
                                        <div className="cred">申请人撤销申请</div></div>)
                } />);
//debugger
            resultSteps =   (<Steps current={stepdata.step}>
                                {step}
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
                        <img src={require('../../assets/avarter.png')} style={{marginTop:5}} />
                    </div>
                    <div className={styles.load_tit}>
                        <h2>{stepdata.title}</h2>
                        <Row>
                            <Col xs={{ span: 5}} lg={{ span: 8}}>发起人：{ name }</Col>
                            <Col xs={{ span: 11}} lg={{ span: 8}}>抄送人：{ copypersonal==''? '--':copypersonal }</Col>
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