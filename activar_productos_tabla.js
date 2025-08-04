const axios = require('axios');

// Configuración - cambiar según tu setup
const BASE_URL = 'http://localhost:8000'; // Cambiar por tu URL del backend
const APP_KEY = 'tu_app_key_aqui'; // Cambiar por tu App-Key actual

async function activarProductosTabla() {
    try {
        console.log('🔍 Buscando la configuración productos_tabla...');
        
        // Primero obtenemos la información de la app para encontrar el setting
        const response = await axios.get(`${BASE_URL}/api/getApp`, {
            headers: {
                'App-Key': APP_KEY,
                'Content-Type': 'application/json'
            }
        });
        
        const app = response.data;
        console.log('✅ App obtenida:', app.Name);
        
        // Buscar el módulo de productos
        const productosModule = app.Modules.find(module => module.key === 'productos');
        if (!productosModule) {
            console.log('❌ No se encontró el módulo de productos');
            return;
        }
        
        console.log('✅ Módulo productos encontrado');
        
        // Buscar el setting productos_tabla
        const productosTableSetting = productosModule.settings.find(setting => setting.key === 'productos_tabla');
        if (!productosTableSetting) {
            console.log('❌ No se encontró el setting productos_tabla');
            return;
        }
        
        console.log('✅ Setting productos_tabla encontrado:');
        console.log('   - ID relacional:', productosTableSetting.relid);
        console.log('   - Estado actual:', productosTableSetting.active ? 'ACTIVADO' : 'DESACTIVADO');
        
        if (productosTableSetting.active) {
            console.log('✨ La configuración ya está ACTIVADA. Deberías ver la tabla de productos.');
            return;
        }
        
        // Activar el setting
        console.log('🔧 Activando configuración productos_tabla...');
        
        const formData = new FormData();
        formData.append('value', '1');
        
        const activateResponse = await axios.put(
            `${BASE_URL}/api/module/setting/${productosTableSetting.relid}`,
            formData,
            {
                headers: {
                    'App-Key': APP_KEY,
                    'Content-Type': 'multipart/form-data'
                }
            }
        );
        
        if (activateResponse.status === 200) {
            console.log('✅ ¡Configuración activada exitosamente!');
            console.log('✨ Ahora deberías ver la TABLA de productos en lugar de iconos');
            console.log('🔄 Reinicia la aplicación o refresca la página de productos');
        } else {
            console.log('❌ Error al activar configuración:', activateResponse.data);
        }
        
    } catch (error) {
        console.log('❌ Error:', error.message);
        if (error.response) {
            console.log('   - Status:', error.response.status);
            console.log('   - Data:', error.response.data);
        }
    }
}

// Ejecutar
activarProductosTabla();
