import Connection from '../../helpers/Connection.js';
import BaseUrl from '../../helpers/baseUrl.js';

export async function sendWhatsApp(context, data) {
  try {
    let url = BaseUrl.getUrl("api/local/send-whatsapp");
    
    // Crear FormData para enviar correctamente al backend Laravel
    const formData = new FormData();
    formData.append('to', data.to);
    formData.append('message', data.message);
    if (data.pedido_id) {
      formData.append('pedido_id', data.pedido_id);
    }
    
    const response = await Connection.request("post", url, formData);
    return response;
  } catch (error) {
    console.error('Error en sendWhatsApp:', error);
    return {
      success: false,
      message: error.message || 'Error al enviar WhatsApp'
    };
  }
}
