import Connection from '../../helpers/Connection.js';
import BaseUrl from '../../helpers/BaseUrl.js';

export async function addModule(context, data) {
  const url = BaseUrl.getUrl('api/app/' + data.id + '/addModule');
  const request = await Connection.request('post',url, data.data);
  return request;
}

export async function getModules (context, filtersData="") {
  const url = BaseUrl.getUrl('api/modules' + filtersData);
  const request = await Connection.request('get',url);
  context.commit('setProperty', { key: 'modules', data: request.data });
}

export async function getModulesOptions (context) {
  const url = BaseUrl.getUrl('api/list/module');
  const request = await Connection.request('get',url);
  context.commit('setProperty', { key: 'modulesOptinos', data: request.data });
}

export async function getModulesApp (context, id) {
  const url = BaseUrl.getUrl('api/modules/of/app/' + id);
  const request = await Connection.request('get',url);
  return request;
}

export async function actualizationModule (context, id) {
  const url = BaseUrl.getUrl('api/module/update/'+ id);
  const request = await Connection.request('post',url);
  return request;
}

export async function activeDesactiveSetting (context, data) {
  const url = BaseUrl.getUrl('api/module/setting/' + data.id);
  const request = await Connection.request('put',url,data.data);
  return request;
}

export async function deleteModule (context, id) {
  const url = BaseUrl.getUrl('api/modules/of/rel/' + id);
  const request = await Connection.request('DELETE',url);
  return request;
}
