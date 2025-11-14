import Connection from '../../helpers/Connection.js';
import BaseUrl from '../../helpers/BaseUrl.js';

export async function getclients (context, filtersData="") {
  const url = BaseUrl.getUrl('api/clients' + filtersData);
  const request = await Connection.request('get',url);
  context.commit('setProperty', { key: 'clients', data: request.data });
}
export async function getclientRut (context, rut = "" ) {
  const url = BaseUrl.getUrl('api/clients?rutOfClient=' + rut);
  const request = await Connection.request('get',url);
  context.commit('setProperty', { key: 'clients', data: request.data });
}
export async function getClientAplicacions(context, id) {
  const url = BaseUrl.getUrl('api/client/' + id);
  const request = await Connection.request('get',url);
  return request;
}
