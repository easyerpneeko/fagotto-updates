import Connection from '../../helpers/Connection.js';
import BaseUrl from '../../helpers/baseUrl.js';


export async function getProducts(context, data = "",) {
  let url = BaseUrl.getUrl('api/local/products' + data);
  const request = await Connection.request('get',url);
  return request;
}

export async function getHistoryProducts(context, data = "",) {
  let url = BaseUrl.getUrl('api/local/history/products' + data);
  const request = await Connection.request('get',url);
  return request;
}

export async function editStock(context, data) {
  let url = BaseUrl.getUrl('api/local/product/stock');
  const request = await Connection.request('post',url,data);
  return request;
}

export async function addToCombo(context, data) {
  let url = BaseUrl.getUrl('api/local/product/addtocombo');
  const request = await Connection.request('post',url,data);
  return request;
}

export async function getProductsOfSell(context) {
  let url = BaseUrl.getUrl('api/local/products/sell');
  const request = await Connection.request('get',url);
  return request;
}

export async function getProductsOfIndex(context) {
  let url = BaseUrl.getUrl('api/local/products/index');
  const request = await Connection.request('get',url);
  return request;
}

export async function getProductsOfFagotto(context) {
  let url = BaseUrl.getUrl('api/local/products/fagotto');
  const request = await Connection.request('get',url);
  return request;
}

export async function newProduct(context, data) {
  let url = BaseUrl.getUrl('api/local/product');
  const request = await Connection.request('post',url,data);
  return request;
}

export async function newProductSell(context, data) {
  let url = BaseUrl.getUrl('api/local/product/new/sell');
  const request = await Connection.request('post',url,data);
  return request;
}

export async function getSellFollows(context, data = "") {
  let url = BaseUrl.getUrl('api/local/products/sell/follows' +data);
  const request = await Connection.request('get',url);
  return request;
}


export async function editProduct(context, data) {
  let url = BaseUrl.getUrl('api/local/product/'+data.id);
  const request = await Connection.request('put',url,data.data);
  return request;
}

export async function deleteProduct(context, id) {
  let url = BaseUrl.getUrl('api/local/product/'+id+'/delete');
  const request = await Connection.request('put',url);
  return request;
}
// this.$store.getters['products/categories']
export async function getCategories(context) {
  let url = BaseUrl.getUrl('api/local/categories');
  const request = await Connection.request('get',url);
  if (request.success){
    context.commit('setProperty',{ key:'categories' , data: request.data });
  }
  return request;
}

export async function newCategory(context, data) {
  let url = BaseUrl.getUrl('api/local/category');
  const request = await Connection.request('post',url,data);
  return request;
}

export async function deleteCategory(context, id) {
  let url = BaseUrl.getUrl('api/local/category/'+id+'/delete');
  const request = await Connection.request('put',url);
  return request;
}

export async function exportProducts(context) {
  let url = BaseUrl.getUrl('api/local/product/export');
  const request = await Connection.request('get',url);
  return request;
}

export async function hideCategory(context, id) {
  let url = BaseUrl.getUrl('api/local/category/'+id+'/hide');
  const request = await Connection.request('put',url);
  return request;
}

export async function showCategory(context, id) {
  let url = BaseUrl.getUrl('api/local/category/'+id+'/show');
  const request = await Connection.request('put',url);
  return request;
}