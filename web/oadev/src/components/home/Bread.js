import React from 'react'
import PropTypes from 'prop-types'
import { Breadcrumb, Icon } from 'antd'
import { Router, Route, Link, hashHistory } from 'react-router';
import styles from './Bread.less'
import pathToRegexp from 'path-to-regexp'

const Bread = ({ routes, params ,children }) => {
  return(<Breadcrumb routes={routes} params={params} />);
}

Bread.propTypes = {
  menu: PropTypes.array,
  routes:PropTypes.array,
  params:PropTypes.array,
}

export default Bread
