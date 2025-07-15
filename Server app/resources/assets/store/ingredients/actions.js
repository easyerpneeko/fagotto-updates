import Connection from '../../helpers/Connection.js';
import BaseUrl from '../../helpers/BaseUrl.js';

export async function update(context, data) {
  const url = BaseUrl.getUrl('api/ingredient/' + data.id);
  const request = await Connection.request('put',url, data.data);
  return request;
}

export async function index(context, params) {
  const url = BaseUrl.getUrl('api/ingredients' + params);
  const request = await Connection.request('get',url);
  context.commit('setProperty', {key:'ingredients', data: request.data});
  return request;
}
