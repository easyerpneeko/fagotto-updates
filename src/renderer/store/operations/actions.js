import Connection from '../../helpers/Connection.js';
import BaseUrl from '../../helpers/baseUrl.js';

export async function newOperation(context, data) {
  let url = BaseUrl.getUrl("api/local/operation");
  const request = await Connection.request("post", url, data);
  return request;
}

export async function getOperations(context, params) {
  let url = BaseUrl.getUrl('api/local/operations' + params);
  const request = await Connection.request('get',url);
  return request;
}

export async function editOperation(context, data) {
  let url = BaseUrl.getUrl("api/local/operation/" + data.id);
  const request = await Connection.request("put", url, data.data);
  return request;
}

export async function deleteOperations(context, id) {
  let url = BaseUrl.getUrl("api/local/operation/" + id + "/delete");
  const request = await Connection.request("delete", url);
  return request;
}

export async function removeOperation(context, id) {
  let url = BaseUrl.getUrl('api/local/operation/' + id);
  const request = await Connection.request('delete',url);
  return request;
}

export async function updateOperation(context, data) {
  let url = BaseUrl.getUrl('api/local/operation/review/' + data.id);
  const request = await Connection.request('put',url, data.data);
  return request;
}