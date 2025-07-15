import Connection from '../../helpers/Connection.js';
import BaseUrl from '../../helpers/BaseUrl.js';

export async function getSubmodules (context, id) {
  const url = BaseUrl.getUrl('api/submodule/' + id);
  const request = await Connection.request('get',url);
  return request;
}

export async function getSubodulesOptions (context) {
  const url = BaseUrl.getUrl('api/list/submodule');
  const request = await Connection.request('get',url);
  context.commit('setProperty', { key: 'submodulesOptinos', data: request.data });
}

export async function getSubodulesOptionsById (context, moduleId = null) {
  const url = BaseUrl.getUrl('api/list/submodule?module=' + moduleId);
  const request = await Connection.request('get',url);
  return request;
}

export async function addSubmodule (context, data) {
  const url = BaseUrl.getUrl('api/app/' + data.id + '/addSubModule');
  const request = await Connection.request('post',url,data.data);
  return request;
}

export async function actualizationsubmodule (context, id) {
  const url = BaseUrl.getUrl('api/submodule/update/'+ id);
  const request = await Connection.request('post',url);
  return request;
}

export async function activeDesactiveSetting (context, data) {
  const url = BaseUrl.getUrl('api/submodule/setting/' + data.id);
  const request = await Connection.request('put',url,data.data);
  return request;
}

export async function deleteSubmodule (context, id) {
  const url = BaseUrl.getUrl('api/submodules/of/rel/rel/' + id);
  const request = await Connection.request('DELETE',url);
  return request;
}
