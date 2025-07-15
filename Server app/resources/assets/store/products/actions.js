import Connection from '../../helpers/Connection.js';
import BaseUrl from '../../helpers/BaseUrl.js';

export async function store(context, data) {
  const url = BaseUrl.getUrl('api/product');
  const request = await Connection.request('post',url, data);
  return request;
}
export async function update(context, data) {
  const url = BaseUrl.getUrl('api/product/' + data.id);
  const request = await Connection.request('put',url, data.data);
  return request;
}
export async function remove(context, id) {
  const url = BaseUrl.getUrl('api/product/' + id);
  const request = await Connection.request('delete',url);
  return request;
}
export async function index(context, params) {
  const url = BaseUrl.getUrl('api/products/' + params);
  const request = await Connection.request('get',url);
  context.commit('setProperty', {key:'products', data: request.data});
  return request;
}
