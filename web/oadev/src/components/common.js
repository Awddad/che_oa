import _ from 'underscore';
import WebStorage from 'react-webstorage';
const webStorage = new WebStorage(window.sessionStorage || window.localStorage);

export const chkPms = (pids)=>{
  //return true;
  let hasPms = false;
  let _value = getCookie('adminPms') || webStorage.getItem('adminPms');
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
export const getCookie = (name)=>{
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

export const key = ()=>{
    let key = "key"+Math.floor(Math.random()*500000);
    return key;
}



export const host = window.location.host== "localhost:8989" ? "http://192.168.1.128:8010" : '';

export const deff_time = (time)=> {
    let s = time % 60;
    let m = Math.floor((time/60)%60);
    let h = Math.floor((time/3600)%24);
    let d = Math.floor((time/3600)/24);
    let result_time=null;
    if(d <= 0){
      if(h<=0){
        if(m>0){
            result_time =  m + "分" + s +'秒';
        }else{
            if(s>0){
              result_time = s +'秒';
            }else{
              result_time = 0;
            }
        }
      }else{
        result_time = h + "小时" + m + "分" + s +'秒';
      }
    }else{
      result_time =d + "天"+ h + "小时" + m + "分" + s +'秒';
    }

    return result_time;
}

export const userLogin = () =>{
    let username = getCookie("username");
    if(username == null){
        window.location.href ="http://test.sso.checheng.net/login.php";
    }
}

