import Connection from '../../helpers/Connection.js';
import BaseUrl from '../../helpers/BaseUrl.js';

export async function getData (context,) {
  const url = BaseUrl.getUrl('api/dashboard');
  const request = await Connection.request('get',url);
  context.commit('setProperty', { key: 'dataGeneral', data: request.data });
}
