import Connection from '../../helpers/Connection.js';
import BaseUrl from '../../helpers/baseUrl.js';

export async function newRequest(context, data) {
  let url = BaseUrl.getUrl("api/local/request");
  const request = await Connection.request("post", url, data);
  return request;
}

export async function getRequests(context, params) {
  let url = BaseUrl.getUrl('api/local/requests' + params);
  const request = await Connection.request('get',url);
  return request;
}

export async function editRequests(context, data) {
  let url = BaseUrl.getUrl("api/local/request/" + data.id);
  const request = await Connection.request("put", url, data.data);
  return request;
}

export async function deleteRequests(context, id) {
  let url = BaseUrl.getUrl("api/local/request/" + id + "/delete");
  const request = await Connection.request("delete", url);
  return request;
}

export async function getProductsOfSell(context) {
  let url = BaseUrl.getUrl('api/products/sell');
  const request = await Connection.request('get',url);
  return request;
}


// export async function index(context, params) {
//   const url = BaseUrl.getUrl('api/products/' + params);
//   const request = await Connection.request('get',url);
//   context.commit('setProperty', {key:'products', data: request.data});
//   return request;
// }
