import Connection from '../../helpers/Connection.js';
import BaseUrl from '../../helpers/BaseUrl.js';

export async function index(context, params = '') {
  const url = BaseUrl.getUrl('api/discounts' + params);
  const request = await Connection.request('get', url);
  context.commit('setProperty', {key:'discounts', data: request.data});
  return request;
}

export async function store(context, data) {
  const url = BaseUrl.getUrl('api/discount');
  const request = await Connection.request('post', url, data);
  return request;
}

export async function update(context, data) {
  const url = BaseUrl.getUrl('api/discount/' + data.id);
  const request = await Connection.request('put', url, data.data);
  return request;
}

export async function remove(context, id) {
  const url = BaseUrl.getUrl('api/discount/' + id);
  const request = await Connection.request('delete', url);
  return request;
}

export async function toggle(context, id) {
  const url = BaseUrl.getUrl('api/discount/' + id + '/toggle');
  const request = await Connection.request('put', url);
  return request;
}

export async function cloneDiscount(context, data) {
  const url = BaseUrl.getUrl('api/discount/' + data.id + '/clone');
  const request = await Connection.request('post', url, data.data);
  return request;
}

export async function getBranches(context) {
  const url = BaseUrl.getUrl('api/discount/branches');
  const request = await Connection.request('get', url);
  context.commit('setProperty', {key:'branches', data: request.data});
  return request;
}

export async function getActiveDiscountsByBranch(context, applicationId) {
  const url = BaseUrl.getUrl('api/discount/branch/' + applicationId);
  const request = await Connection.request('get', url);
  return request;
}

export async function checkProductDiscount(context, data) {
  const url = BaseUrl.getUrl('api/discount/check');
  const request = await Connection.request('post', url, data);
  return request;
}
