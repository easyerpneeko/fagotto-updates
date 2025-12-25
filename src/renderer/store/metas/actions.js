import Connection from '@/helpers/Connection.js';
import BaseUrl from '@/helpers/baseUrl.js';

export const fetchCurrentMeta = async ({ commit }) => {
  try {
    const url = BaseUrl.getUrl('api/metas-locales/current');
    const response = await Connection.request('get', url);
    
    if (response && response.success && response.data.meta) {
      commit('SET_CURRENT_META', response.data.meta);
      return { success: true, data: response.data.meta };
    } else {
      commit('SET_CURRENT_META', null);
      return { success: false, data: null };
    }
  } catch (error) {
    console.error('Error al obtener meta del local:', error);
    commit('SET_CURRENT_META', null);
    return { success: false, error: error };
  }
}
