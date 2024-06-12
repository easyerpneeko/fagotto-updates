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

// export async function updateOperation(context, data) {
//   let url = BaseUrl.getUrl('api/local/operation/review/' + data.id);
//   const request = await Connection.request('put',url, data.data);
//   return request;
// }

//CATEGORIAS
export async function getCategories(context) {
  let url = BaseUrl.getUrl('api/local/operation/categories');
  const request = await Connection.request('get',url);
  if (request.success){
    context.commit('setProperty',{ key:'categories' , data: request.data });
  }
  return request;
}

export async function removeCategory(context, data) {
  let url = BaseUrl.getUrl('api/local/operation/categories/' + data);
  const request = await Connection.request('delete',url, data);
  return request;
}

export async function newCategory(context, data) {
  let url = BaseUrl.getUrl("api/local/operation/categories");
  const request = await Connection.request("post", url, data);
  return request;
}

export async function editCategory(context, data) {
  let url = BaseUrl.getUrl("api/local/operation/categories/edit/"+data.id);
  const request = await Connection.request("put", url, data.data);
  return request;
}

//SUBCATEGORIAS
export async function getSubcategories(context) {
  let url = BaseUrl.getUrl('api/local/operation/subcategories');
  const request = await Connection.request('get',url);
  if (request.success){
    context.commit('setProperty',{ key:'subcategories' , data: request.data });
  }
  return request;
}

export async function removeSubcategory(context, data) {
  let url = BaseUrl.getUrl('api/local/operation/subcategories/' + data);
  const request = await Connection.request('delete',url, data);
  return request;
}

export async function editSubcategory(context, data) {
  let url = BaseUrl.getUrl("api/local/operation/subcategories/edit/"+data.id);
  const request = await Connection.request("put", url, data.data);
  return request;
}

export async function newSubcategory(context, data) {
  let url = BaseUrl.getUrl("api/local/operation/subcategories");
  const request = await Connection.request("post", url, data);
  return request;
}

export async function getSubcategoriesByCategory(context, data) {
  console.log(data);
  let url = BaseUrl.getUrl('api/local/operation/subcategories/'+data);
  const request = await Connection.request('get',url);
  // if (request.success){
  //   context.commit('setProperty',{ key:'subcategories' , data: request.data });
  // }
  return request;
}

//Balances
export async function getBalances(context, params) {
  let url = BaseUrl.getUrl('api/local/operations/balances' + params);
  const request = await Connection.request('get',url);
  return request;
}

