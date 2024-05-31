import Connection from '../../helpers/Connection.js';
import BaseUrl from '../../helpers/baseUrl.js';

export async function getDevolutions(context, params) {
  let url = BaseUrl.getUrl('api/local/sells/devolutions' + params);
  const request = await Connection.request('get',url);
  return request;
}

export async function devolutionProductSell(context, data) {
  let url = BaseUrl.getUrl('api/local/sell/devolution/product/'+data.params.product_sell_id);  
  const request = await Connection.request('put', url, data.formData);
  return request;
}