import Connection from '../../helpers/Connection.js';
import BaseUrl from '../../helpers/BaseUrl.js';

export async function newApp (context, data) {
  const url = BaseUrl.getUrl('api/aplication');
  const request = await Connection.request('post',url, data);
  return request;
}

export async function addTime (context, data) {
  const url = BaseUrl.getUrl('api/aplication/' + data.id);
  const request = await Connection.request('put',url, data.data);
  return request;
}

export async function cutService (context, id) {
  const url = BaseUrl.getUrl('api/cutService/' + id);
  const request = await Connection.request('put',url);
  return request;
}

export async function activeService (context, id) {
  const url = BaseUrl.getUrl('api/activeService/' + id);
  const request = await Connection.request('put',url);
  return request;
}

export async function executeMigrate (context, id) {
  const url = BaseUrl.getUrl('api/module/migrate/' + id);
  const request = await Connection.request('post',url);
  return request;
}

export async function executeMigrateSubmodule (context, id) {
  const url = BaseUrl.getUrl('api/submodule/migrate/' + id);
  const request = await Connection.request('post',url);
  return request;
}

export async function getAllAplications (context) {
  const url = BaseUrl.getUrl('api/aplications/all');
  const request = await Connection.request('get',url);
  context.commit('setProperty', { key: 'allAplications', data: request.data });
}

export async function getAplications (context, filtersData = "") {
  const url = BaseUrl.getUrl('api/aplications' + filtersData);
  const request = await Connection.request('get',url);
  context.commit('setProperty', { key: 'aplications', data: request.data });
}

export async function getAplicationRut (context, rut = "" ) {
  const url = BaseUrl.getUrl('api/aplications?rutOfClient=' + rut);
  const request = await Connection.request('get',url);
  context.commit('setProperty', { key: 'aplications', data: request.data });
}

export async function getAplication (context, id) {
  const url = BaseUrl.getUrl('api/getAppById/' + id);
  const request = await Connection.request('get',url);
  context.commit('setProperty', { key: 'permisos', data: request.data.Permisos });
  context.commit('setProperty', { key: 'app', data: request.data });
  return request;
}

export async function getRut(context, rut) {
  const url = BaseUrl.getUrl('api/rut/' + rut);
  const request = await Connection.request('get',url);
  return request;
}

export async function newUserApp (context, data) {
  const url = BaseUrl.getUrl('api/global/userCreate/' + data.id);
  const request = await Connection.request('post',url, data.data);
  return request;
}
export async function editUserApp (context, data) {
  const url = BaseUrl.getUrl('api/global/useredit/' + data.idUser + '/' + data.id);
  const request = await Connection.request('put',url, data.data);
  return request;
}
export async function trashUserApp (context, data) {
  const url = BaseUrl.getUrl('api/global/user/' + data.idUser + '/delete/' + data.id);
  const request = await Connection.request('delete',url, null);
  return request;
}
export async function getUserApp(context, params) {
  const url = BaseUrl.getUrl('api/global/users/' + params);
  const request = await Connection.request('get',url);
  context.commit('setProperty', { key: 'usersApp', data: request.data });
  return request;
}
export async function delteUserApp (context, data) {
  const url = BaseUrl.getUrl('api/global/userTrash/' + data.idUser + '/' + data.id);
  const request = await Connection.request('put',url, data.data);
  return request;
}

export async function newTypeUser (context, data) {
  const url = BaseUrl.getUrl('api/global/typeUserCreate/' + data.id);
  const request = await Connection.request('post',url, data.data);
  return request;
}
export async function editTypeUser (context, data) {
  const url = BaseUrl.getUrl('api/global/typeUser/' + data.idApp + '/' + data.id);
  const request = await Connection.request('put',url, data.data);
  return request;
}
export async function getTypeUser(context, id) {
  const url = BaseUrl.getUrl('api/global/role/' + id);
  const request = await Connection.request('get',url);
  context.commit('setProperty', { key: 'roles', data: request.data });
  return request;
}
export async function trashTypeUser (context, data) {
  const url = BaseUrl.getUrl('api/global/role/' + data.idRole + '/delete/' + data.id);
  const request = await Connection.request('delete',url, null);
  return request;
}
export async function modifyEnvs (context, data) {
  const url = BaseUrl.getUrl('api/app/' + data.id +'/envs');
  const request = await Connection.request('put',url, data.data);
  return request;
}

// Folios
export async function sendFolios (context, data) {
  const url = BaseUrl.getUrl('api/folios/' + data.id);
  const request = await Connection.request('post',url,data.data);
  return request;
}
export async function getFolios (context, id) {
  const url = BaseUrl.getUrl('api/folios/' + id);
  const request = await Connection.request('get',url);
  return request;
}
