import fetch from 'dva/fetch';

function parseJSON(response) {
  return response.json();
}

function checkStatus(response) {
  if (response.code==200){
    return response;
  }
  //console.log(response);
  const error = new Error(response);
  error.response = response;
  throw error;
}

/**
 * Requests a URL, returning a promise.
 *
 * @param  {string} url       The URL we want to request
 * @param  {object} [options] The options we want to pass to "fetch"
 * @return {object}           An object containing either "data" or "err"
 */
export default function request(url, options) {
  options = options || {};
  options = {...options,credentials: 'same-origin'};
  return fetch(url, options)
    .then(parseJSON)
    .then(checkStatus)
    .then(data => ({ data }))
    //.catch(err => ({ err }));
}
