import Connection from '../../helpers/Connection.js';
import BaseUrl from '../../helpers/BaseUrl.js';

export function setVerifyIfNotDefined(context){
  if (!context.state.timerOfVerify) {
    const intervalo = setInterval(() => {
      if (!context.dispatch('verifyTokenAlive')) {
        context.dispatch('LogoutUser');
        clearInterval(context.state.timerOfVerify);
        context.commit('setProperty',{ key:'timerOfVerify' , data: null });
      }
    }, 1000*60+1);
    context.commit('setProperty',{ key:'timerOfVerify' , data: intervalo });
  }
}

function Logout(context){
  Connection.desfillHeaders();
  context.commit('setProperty',{ key:'token' , data: null });
  context.commit('setProperty',{ key:'user' , data: null });
  let storageAuth = null;
  window.location.replace(BaseUrl.getUrl('/login'));
}

export async function LogoutUser (context) {
  Logout(context);
}

export async function verifyTokenAlive (context) {
  const url = BaseUrl.getUrl('api/me');
  const request = await Connection.request( 'get', url );
  if (!request.success){
    if (request.exitLogin) {
        Logout(context);
        return false;
    }
  }
  return true;
}
