import store from '../store/users';

export default class BaseUrl {

  static getUrl(route){
    return store.state.apiUrl + '/' + route;
  }

}
