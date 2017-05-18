import _ from 'underscore';
import WebStorage from 'react-webstorage';
const webStorage = new WebStorage(window.sessionStorage || window.localStorage);

export const chkPms = (pids)=>{
  return true;
  let hasPms = false;
  let _value = webStorage.getItem('adminPms');
  if(!(pids instanceof Array)) return hasPms;
  if(_value==undefined || _value==null || _value.length==0) return hasPms;
  let adminPms = _value.split(',');
  if(pids.length==1){
    hasPms  = (_.indexOf(adminPms,pids[0])>=0);
  }else{
    hasPms = (_.intersection(adminPms,pids).length>0);
  }
  return hasPms;
}
/**
 * 菜单
 * 如果元素display:'block'
 * @param pids 权限ID组
 * @param style 组件的样式
 * @return style
 */
export const chkPmsForBlock = (pids,style)=>{
  style = style || {};
  return {...style,display:chkPms(pids)?'block':'none'};
}

/**
 * A标签
 * 如果元素display:'inline'
 */
export const chkPmsForInline = (pids,style)=>{
  style = style || {};
  return {...style,display:chkPms(pids)?'inline':'none'};
}

/**
 * 按钮
 * 如果元素display:'inline-block'
 */
export const chkPmsForInlineBlock = (pids,style)=>{
  style = style || {};
  return {...style,display:chkPms(pids)?'inline-block':'none'};
}

/**
 * Cookie
 * 设置Cookie
 */
export const setCookie = (name,value) =>{
    let exp = new Date();
        exp.setTime(exp.getTime() + 30*60*1000);
        document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
}

/**
 * Cookie
 * 读取Cookie内容
 */
export const getCoookie = (name)=>{
    let arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
        if(arr=document.cookie.match(reg))
            return unescape(arr[2]);
        else
            return null;
}
/**
 * Cookie
 * 删除Cookie
 */
export const delCookie = (name)=>{
    let exp = new Date();
        exp.setTime(exp.getTime() - 1);
    let cval=getCookie(name);
        if(cval!=null) document.cookie= name + "="+cval+";expires="+exp.toGMTString();
}