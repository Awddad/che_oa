import request from '../utils/request';
import qs from 'qs';


export async function login(params) {
  return request('/admin/AdminCtrl/login', {
    method: 'post',
    headers: {
      "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
    },
    body: qs.stringify(params),
  });
}

export async function updatePassword(params) {
	  return request('/admin/AdminCtrl/updatePassword', {
	    method: 'post',
	    headers: {
	      "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
	    },
	    body: qs.stringify(params),
	  });
	}


export async function logout(params) {
	return request(`/admin/AdminCtrl/logout?${qs.stringify(params)}`);
}
