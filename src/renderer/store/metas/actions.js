import Connection from '../../helpers/Connection.js';
import BaseUrl from '../../helpers/baseUrl.js';

export const fetchCurrentMeta = async ({ commit }) => {
  try {
    const url = BaseUrl.getUrl('api/metas-locales/current');
    const response = await Connection.request('get', url);
    
    console.log('📊 [METAS ACTION] Respuesta completa del API:', response);
    
    if (response && response.success) {
      // El API devuelve: { success: true, meta: {...}, meta_semanal, dias_semana, etc }
      // Necesitamos extraer el objeto 'meta' que contiene 'meta_diaria'
      const metaData = response.data;
      console.log('📊 [METAS ACTION] metaData:', metaData);
      
      // Si el API devuelve la estructura con 'meta' anidado, extraerlo
      const metaToStore = metaData.meta || metaData;
      console.log('📊 [METAS ACTION] Meta a guardar:', metaToStore);
      
      commit('SET_CURRENT_META', metaToStore);
      return { success: true, data: metaToStore };
    } else {
      console.log('📊 [METAS ACTION] No se encontró meta o respuesta no exitosa');
      commit('SET_CURRENT_META', null);
      return { success: false, data: null };
    }
  } catch (error) {
    console.error('❌ [METAS ACTION] Error al obtener meta del local:', error);
    commit('SET_CURRENT_META', null);
    return { success: false, error: error };
  }
}
