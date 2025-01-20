import Connection from '../../helpers/Connection.js';
import BaseUrl from '../../helpers/baseUrl.js';

export async function getSells(context, data) {
  let url = BaseUrl.getUrl('api/local/report/sells' + data.params);
  const request = await Connection.request('get',url, data.data);
  if(!request.success) return false;
  getCounters(context,data);
  return request;
}

export async function getCounters(context, data) {
  let url = BaseUrl.getUrl('api/local/report/counters'+ data.params);
  const request = await Connection.request('get',url, data.data);
  if(request.success){
    context.commit('setProperty', { key: 'counters', data: request.data.counters });
    context.commit('setProperty', { key: 'products', data: request.data.products });
    context.commit('setProperty', { key: 'waiters', data: request.data.waiters });
    context.commit('setProperty', { key: 'expenses', data: request.data.expenses });
    context.commit('setProperty', { key: 'workshifts', data: request.data.workshifts });
  }
}
export async function getOneWaiter(context, data) {
  console.log(":::::::::::: GetOneWaiter :::::::::::::", {context, data})
  //data.waiter_id = 1;
  context.commit("clearOneWaiter")
  let url = BaseUrl.getUrl('api/local/report/waiter'+data.params);
  const request = await Connection.request('get',url,data.data);
  if (request.success) {
    request.data.report.map((value, index) => {
      request.data.report[index].products = JSON.parse(value.products)
    })
    console.log("::::::::::::: SUCCESS:GetOneWaiter:SUCCESS :::::::::",request.data);
    context.commit('setProperty', {key: 'oneWaiter', data: request.data});
  }
}
export async function printPDF(context, data) {
  let url = BaseUrl.getUrl('api/local/reports/pdf' + data.params);
  const request = await Connection.request('post',url,data.data);
  return request;
}

// export async function getTopSells(context, data) {
//   let url = BaseUrl.getUrl('api/local/report/sells/top' + data.params);
//   const request = await Connection.request('get',url, data.data);
//   if(!request.success) return false;
//   return request;
// }

// export async function getSellsByHour(context, data) {
//   let url = BaseUrl.getUrl('api/local/report/sells/byhour' + data.params);
//   const request = await Connection.request('get',url, data.data);
//   if(!request.success) return false;
//   return request;
// }