import React,{PropTypes} from 'react';
import { routerRedux } from 'dva/router';
import styles from './style.less';

const Loading = React.createClass({
    render(){
        return(
            <div className={styles.loading}>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </div>
        );
    }
});

Loading.propTypes = {
   location: PropTypes.object,
   dispatch: PropTypes.func
};

export default Loading;