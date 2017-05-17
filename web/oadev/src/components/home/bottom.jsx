import React, { PropTypes } from 'react';
import { Menu, Icon } from 'antd';
import { Link } from 'dva/router';
import styles from './main.less'
function getMenuKeyFromUrl(pathname) {
  let key = '';
  try {
    key = pathname.match(/\/([^\/]*)/i)[1];
    /* eslint no-empty:0 */
  } catch (e) {}
  return key;
}

function Bottom({ location }) {
  return (
    <div className={styles.foot}>OA系统后台管理V1.0.0 版权所有 © 2016 www.che.com</div>
);
}

Bottom.propTypes = {
  location: PropTypes.object,
};

export default Bottom;
