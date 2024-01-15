import Connection from '../../helpers/Connection.js';
import EchoHelper from '../../helpers/EchoHelper.js';
import BaseUrl from '../../helpers/baseUrl.js';
import store from '../main';


export async function getFeeds(context) {
  let url = BaseUrl.getUrl('api/local/feeds');
  const request = await Connection.request('get',url);
  context.commit('setProperty', {key: 'feeds', data: request.data})
  return request;
}
export async function getEnvs(context) {
  let url = BaseUrl.getUrl('api/getEnvs');
  const request = await Connection.request('get',url);
  context.commit('setProperty', {key: 'envs', data: request.data})
  return request;
}
export async function sendSerial(context, data) {
  let url = BaseUrl.getUrl('api/getApp');
  Connection.fillAppHeader(data);
  EchoHelper.fillAppHeader(data);
  const request = await Connection.request('get',url);
  return request;
}

export async function getMyInitMoney(context) {
  let url = BaseUrl.getUrl('api/init/money');
  const request = await Connection.request('get',url);
  return request;
}

export async function setInitMoney(context, data) {
  let url = BaseUrl.getUrl('api/init/money');
  const request = await Connection.request('post',url,data);
  return request;
}

export async function newWorkshift(context, data) {
  let url = BaseUrl.getUrl('api/local/workshift');
  const request = await Connection.request('post',url,data);
  return request;
}

export async function sendLogin(context, data) {
  let url = BaseUrl.getUrl('api/local/login');
  const request = await Connection.request('post',url,data);
  if (request.success){
    Connection.fillHeaders(request.data.token);
    EchoHelper.fillHeaders(request.data.token);
    /*console.log('SE SETTEA EL PROPERTY DEL USER');
    console.log(request.data.user);*/
    context.commit('setProperty',{ key:'user' , data: request.data.user });
    //context.commit('setProperty',{ key:'isLogin' , data: true });
    //console.log(store);
    //store.commit('setProperty',{ key:'user' , data: request.data.user });
  }
  return request;
}

export async function checkAuth(context) {
  let url = BaseUrl.getUrl('api/local/me');
  const request = await Connection.request('get',url);
  if (request.success){
    //Connection.fillHeaders(request.data.token);
    context.commit('setProperty',{ key:'user' , data: null });
    context.commit('setProperty',{ key:'user' , data: request.data });
  }
  return request;
}

export async function refreshData(context, slim = "") {
  let url = BaseUrl.getUrl('api/getApp' + slim);
  const request = await Connection.request('get',url);
  if(slim == ""){
    context.commit('setProperty',{ key:'permisos' , data: request.data.Permisos });
  }
  return request;
}

// Users api
export async function newUser(context, data) {
  let url = BaseUrl.getUrl('api/local/register');
  const request = await Connection.request('post',url,data);
  return request;
}

export async function editUser(context, data) {
  let url = BaseUrl.getUrl('api/local/user/' + data.id);
  const request = await Connection.request('put',url,data.data);
  return request;
}

export async function deleteUser(context, id) {
  let url = BaseUrl.getUrl('api/local/user/'+id+'/delete');
  const request = await Connection.request('put',url);
  return request;
}

export async function getUsers(context, data = "") {
  let url = BaseUrl.getUrl('api/local/users' + data);
  const request = await Connection.request('get',url);
  context.commit('setProperty',{ key:'users' , data: request.data });
  return request;
}

export async function getAllUsers(context, data = "") {
  let url = BaseUrl.getUrl('api/local/all/users' + data);
  const request = await Connection.request('get',url);
  context.commit('setProperty',{ key:'allUsers' , data: request.data });
  return request;
}
// getStore
export function serchItem(context, params) {
  const keyState = params.keyState;
  const data = params.data;

  return context.state[keyState].find((type) => type[data.key] === data.value);
}

export async function getFolios (context, id) {
  const url = BaseUrl.getUrl('api/folios/' + id);
  const request = await Connection.request('get',url);
  return request;
}