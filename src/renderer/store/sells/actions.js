import Connection from '../../helpers/Connection.js';
import BaseUrl from '../../helpers/baseUrl.js';

export async function newTicket(context, data) {
  let url = BaseUrl.getUrl('api/local/ticket');
  console.log("ticket before send: ",data)
  const request = await Connection.request('post', url, data);
  console.log("ticket data:",request)
  return request;
}
export async function editTicket(context, data) {
  let url = BaseUrl.getUrl('api/local/ticket/'+ data.id);
  const request = await Connection.request('put', url, data.data);
  return request;
}
export async function getOrder(context, code) {
  let url = BaseUrl.getUrl('api/local/order/' + code);
  const request = await Connection.request('get',url);
  console.log("order data:", request)
  return request;
}

export async function newSell(context, data) {
  let url = BaseUrl.getUrl('api/local/sell');
  const request = await Connection.request('post', url, data);
  if(request.success)
    context.commit('setProperty',{ key:'sellPast' , data: request.data.id });
  return request;
}

export async function fastSell(context, data) {
  let url = BaseUrl.getUrl('api/local/fastSell');
  const request = await Connection.request('post', url, data);
  if(request.success)
    context.commit('setProperty',{ key:'sellPast' , data: request.data });
  return request;
}

export async function removeSell(context, data) {
  let url = BaseUrl.getUrl('api/local/sell/' + data.id);  
  const request = await Connection.request('put', url, data.formData);
  return request;
}

export async function getDeletedSells(context) {
  let url = BaseUrl.getUrl('api/local/sells/deleted');
  const request = await Connection.request('get',url);
  return request;
}

export async function getSells(context, data = "") {
  let url = BaseUrl.getUrl('api/local/sells' + data);
  const request = await Connection.request('get',url);
  return request;
}

export async function getSell(context, id) {
  let url = BaseUrl.getUrl('api/local/sell/' + id);
  const request = await Connection.request('get',url);
  return request;
}

export async function getClient(context, rut) {
  let url = BaseUrl.getUrl('api/local/client/' + rut);
  const request = await Connection.request('get',url);
  return request;
}

export async function editClient(context, data) {
  let url = BaseUrl.getUrl('api/local/client/'+data.id);
  const request = await Connection.request('post',url,data.data);
  return request;
}

export async function printPDF(context, data) {
  var boleta = (data.boleta)?'?boleta_local=1':'';
  let url = BaseUrl.getUrl('api/local/sell/'+data.id+'/pdf'+boleta);
  const request = await Connection.request('get',url);
  return request;
}

export async function processSell(context, data) {
  let url = BaseUrl.getUrl('api/local/sell/'+data.type+'/'+data.id);
  const request = await Connection.request('post',url,data.data);
  return request;
}

export async function printPDFGuia(context, data) {
  let url = BaseUrl.getUrl('api/local/guia/'+data.id+'/pdf');
  const request = await Connection.request('get',url);
  return request;
}

export async function consultarDTE(context, id) {
  let url = BaseUrl.getUrl('api/local/sell/dte/'+id);
  const request = await Connection.request('get',url);
  return request;
}

export async function cancelFactura(context, id) {
  let url = BaseUrl.getUrl('api/local/sell/nota_de_credito/factura/'+id);
  const request = await Connection.request('put',url);
  return request;
}
export async function cancelBoleta(context, id) {
  let url = BaseUrl.getUrl('api/local/sell/nota_de_credito/boleta/'+id);
  const request = await Connection.request('put',url);
  return request;
}

export async function importProducts(context, data) {
  let url = BaseUrl.getUrl('api/local/product/import');
  const request = await Connection.request('post',url,data);
  return request;
}

export async function getReport(context, params){
  let url = BaseUrl.getUrl('api/local/sells/report' + params);
  const request = await Connection.request('get',url);
  return request;
}
