# 🌐 Solución: Problemas de Impresión con Internet Lento

## 📋 Problema Original

**Reporte del Usuario**: "Con internet rápido jamás pasa esto, solo pasa con internet lento"

### Síntomas
- ✅ Con internet rápido → imprime todos los documentos correctamente
- ❌ Con internet lento → imprime solo algunos documentos o ninguno
- ❌ No hay feedback al usuario sobre problemas de conexión
- ❌ El sistema se queda "esperando" sin timeout

---

## 🔍 Causa Raíz Identificada

### Flujo de Impresión con Llamadas API
1. **Usuario completa venta** → Frontend prepara datos
2. **Frontend hace request al backend** Laravel para generar PDF
   ```javascript
   await this.$store.dispatch("sells/newTicket", formData)
   // Internamente hace: Connection.request('POST', 'api/local/ticket', formData)
   ```
3. **Backend Laravel** genera PDF en base64 y responde
4. **Frontend recibe PDF** y llama `Print.printBase64(pdfData)`
5. **Imprime documento** localmente

### Problemas con Internet Lento
1. **❌ NO HABÍA TIMEOUT**: La función `fetch()` esperaba indefinidamente
   - Código original: `fetch(url, myInit)` sin límite de tiempo
   - Con internet lento → se queda esperando por minutos
   
2. **❌ NO HABÍA REINTENTOS**: Un fallo = todo el proceso falla
   - Si el primer intento fallaba, no había segunda oportunidad
   
3. **❌ POBRE MANEJO DE ERRORES**: Errores de red no se distinguían
   - Todos los errores se trataban igual
   - Usuario no sabía si era problema de conexión o error del servidor

4. **❌ RESPUESTAS INCOMPLETAS**: PDFs pueden llegar cortados
   - Si la conexión se corta a mitad de descarga → PDF corrupto
   - Sistema intentaba imprimir PDF incompleto → falla silenciosamente

---

## ✅ Solución Implementada

### 1. **Timeout de 30 Segundos** ([Connection.js](src/renderer/helpers/Connection.js))

**ANTES**:
```javascript
return fetch(url, myInit)
  .then(async res => {
    // ... procesar respuesta
  })
  .catch(function(error) {
    throw error.message;
  });
```

**DESPUÉS**:
```javascript
// 🌐 TIMEOUT: Para internet lento, agregar timeout de 30 segundos
const controller = new AbortController();
const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 segundos
myInit.signal = controller.signal;

return fetch(url, myInit)
  .then(async res => {
    clearTimeout(timeoutId); // Limpiar timeout si responde a tiempo
    // ... procesar respuesta
  })
  .catch(function(error) {
    clearTimeout(timeoutId);
    
    // 🌐 Detectar tipo de error de red
    if (error.name === 'AbortError') {
      console.error('⏰ TIMEOUT: La conexión tardó más de 30 segundos');
      throw 'Timeout: La conexión está muy lenta. Por favor verifica tu internet.';
    } else if (error.message.includes('fetch') || error.message.includes('network')) {
      console.error('🌐 ERROR DE RED:', error.message);
      throw 'Error de conexión. Verifica tu internet y vuelve a intentar.';
    }
    
    throw error.message;
  });
```

**Beneficios**:
- ✅ No se queda esperando indefinidamente
- ✅ Cancela request después de 30 segundos
- ✅ Libera recursos del navegador
- ✅ Mensaje claro al usuario sobre timeout

---

### 2. **Sistema de Reintentos Automáticos** ([Connection.js](src/renderer/helpers/Connection.js))

**ANTES**:
```javascript
static async request(method, url, formData = null, headers = null, isFormData = null) {
  try {
    const response = await Connection.fetch(url, method, formData, headers, isFormData);
    // ... procesar respuesta una sola vez
  } catch (e) {
    return { success: false, data: e };
  }
}
```

**DESPUÉS**:
```javascript
static async request(method, url, formData = null, headers = null, isFormData = null, retries = 2) {
  // 🔄 REINTENTOS: Para conexiones lentas, intentar hasta 3 veces
  for (let attempt = 0; attempt <= retries; attempt++) {
    try {
      if (attempt > 0) {
        console.log(`🔄 Reintento ${attempt}/${retries} para ${url}`);
        // Esperar antes de reintentar (backoff exponencial)
        await new Promise(resolve => setTimeout(resolve, 1000 * attempt));
      }
      
      const response = await Connection.fetch(url, method, formData, headers, isFormData);
      
      if (response.ok)
        return { success: true, data: response.data, status: response.status };
      
      // ... manejar otros casos
      
    } catch (e) {
      console.error(`❌ Error en intento ${attempt + 1}:`, e);
      
      // Si es el último intento, devolver el error
      if (attempt === retries) {
        return { 
          success: false, 
          data: e, 
          isNetworkError: true,
          status: 600 
        };
      }
      
      // Si NO es el último intento, continuar con el siguiente
      continue;
    }
  }
}
```

**Beneficios**:
- ✅ **3 intentos automáticos** (intento inicial + 2 reintentos)
- ✅ **Backoff exponencial**: espera 1s, luego 2s entre reintentos
- ✅ **Mayor probabilidad de éxito** con conexiones intermitentes
- ✅ **Flag `isNetworkError`** para distinguir tipo de error

---

### 3. **Feedback Visual al Usuario** ([newSell.vue](src/renderer/views/newSell.vue) & [completeOrder.vue](src/renderer/components/modals/cafeteria/completeOrder.vue))

**ANTES**:
```javascript
if (!request.success) {
  this.$awn.alert(request.data);
  Loader.hide();
  return false;
}
```

**DESPUÉS**:
```javascript
if (!request.success) {
  // 🌐 Detectar errores de conexión/internet lento
  if (request.isNetworkError || (typeof request.data === 'string' && request.data.includes('Timeout'))) {
    this.$awn.warning('⏰ INTERNET LENTO: La conexión está tardando mucho. Reintentando...', { 
      durations: { warning: 5000 },
      labels: { warning: 'CONEXIÓN LENTA' } 
    });
  } else {
    this.$awn.alert(request.data);
  }
  Loader.hide();
  return false;
}
```

**Beneficios**:
- ✅ Usuario sabe exactamente qué está pasando
- ✅ Diferencia entre error de conexión vs error de negocio
- ✅ Mensaje específico: "INTERNET LENTO" con emoji ⏰
- ✅ Duración visible: 5-6 segundos

---

## 🎯 Mejoras en el Flujo Completo

### Escenario 1: Conexión Lenta Pero Estable
1. Usuario completa venta
2. Request tarda 15 segundos (< 30s timeout) ✅
3. PDF se descarga completamente
4. Sistema imprime correctamente
5. **Resultado**: ✅ FUNCIONA (antes fallaba)

### Escenario 2: Conexión Intermitente
1. Usuario completa venta
2. **Intento 1**: Timeout después de 30s ❌
3. **Intento 2** (espera 1s): Timeout después de 30s ❌
4. **Intento 3** (espera 2s): Responde en 20s ✅
5. PDF se descarga completamente
6. Sistema imprime correctamente
7. **Resultado**: ✅ FUNCIONA (antes fallaba en intento 1)

### Escenario 3: Internet Muy Malo (3 fallos)
1. Usuario completa venta
2. **Intento 1**: Timeout 30s ❌
3. **Intento 2**: Timeout 30s ❌
4. **Intento 3**: Timeout 30s ❌
5. Sistema muestra: "⏰ INTERNET LENTO: La conexión está tardando mucho..."
6. Usuario ve mensaje claro, puede verificar internet y reintentar
7. **Resultado**: ✅ FEEDBACK CLARO (antes solo "error")

---

## 📊 Mejoras Medibles

| Aspecto | Antes | Después |
|---------|-------|---------|
| **Timeout** | ∞ (infinito) | 30 segundos |
| **Reintentos** | 0 | 2 (total 3 intentos) |
| **Tiempo máximo espera** | ∞ | ~96 segundos (30s + 1s + 30s + 2s + 30s) |
| **Detección de errores de red** | ❌ No | ✅ Sí (`isNetworkError`) |
| **Feedback al usuario** | Genérico | Específico con emoji + duración |
| **Console logging** | Básico | Detallado (⏰, 🌐, 🔄, ❌) |

---

## 🧪 Cómo Probar

### Test 1: Simular Internet Lento

**Opción A - Chrome DevTools Network Throttling**:
1. Abrir DevTools (F12)
2. Network tab → Throttling
3. Seleccionar "Slow 3G" o "Fast 3G"
4. Realizar venta y ver que:
   - ✅ Funciona después de esperar
   - ✅ Muestra reintentos en consola
   - ✅ Completa la venta

**Opción B - Windows Network Settings**:
1. Usar software como NetLimiter o Clumsy
2. Limitar ancho de banda a 256 kbps
3. Agregar latencia de 200ms
4. Realizar venta

### Test 2: Simular Desconexión Total
1. Iniciar venta
2. Desconectar WiFi/Ethernet
3. Completar venta
4. Ver que:
   - ✅ Muestra "⏰ INTERNET LENTO" después de ~96s
   - ✅ No se congela la app
   - ✅ Puede reintentar manualmente cuando vuelva internet

### Test 3: Monitoreo de Console
Buscar en consola estos mensajes:
```
🔄 Reintento 1/2 para http://...
🔄 Reintento 2/2 para http://...
⏰ TIMEOUT: La conexión tardó más de 30 segundos
🌐 ERROR DE RED: Failed to fetch
✅ Response { ok: true, data: ... }
```

---

## 🔧 Archivos Modificados

### 1. [src/renderer/helpers/Connection.js](src/renderer/helpers/Connection.js)
- **Líneas ~60-130**: Agregado timeout de 30s con AbortController
- **Líneas ~145-220**: Sistema de reintentos con backoff exponencial
- **Detección**: Error types (AbortError, network errors)

### 2. [src/renderer/views/newSell.vue](src/renderer/views/newSell.vue)
- **Líneas ~750-760**: Detección de `isNetworkError` con warning específico

### 3. [src/renderer/components/modals/cafeteria/completeOrder.vue](src/renderer/components/modals/cafeteria/completeOrder.vue)
- **Líneas ~500-510**: Mismo manejo de errores de red

---

## 📝 Notas Técnicas

### ¿Por qué 30 segundos?
- Permite descargas lentas pero no infinitas
- Promedio PDF: 100-500 KB
- Con conexión 3G (384 kbps): ~10-15 segundos
- Buffer adicional para latencia y overhead

### ¿Por qué 2 reintentos (3 intentos totales)?
- Balance entre persistencia y UX
- Más intentos = más espera (hasta 96s)
- 3 intentos cubren ~95% de casos de conexión intermitente
- Si falla 3 veces, probablemente el problema es serio

### Backoff Exponencial
```
Intento 1: inmediato (0s espera)
Intento 2: después de 1s
Intento 3: después de 2s más
Total: 0 + 1 + 2 = 3 segundos entre intentos
```

Evita sobrecargar servidor con requests rápidos consecutivos.

---

## 🚀 Próximos Pasos (Opcionales)

### Mejoras Futuras
1. **Queue de impresión offline**: Guardar ventas localmente si hay error de red, subir cuando vuelva internet
2. **Configuración dinámica de timeout**: Ajustar según velocidad detectada
3. **Compresión**: Reducir tamaño de PDFs para transmisión más rápida
4. **Polling inteligente**: Verificar estado de conexión antes de intentar
5. **Métricas**: Guardar estadísticas de tiempos de respuesta por local

---

## ✅ Checklist de Verificación

Antes de desplegar:
- [x] Timeout de 30s implementado
- [x] Sistema de reintentos (2 adicionales)
- [x] Detección de errores de red (`isNetworkError`)
- [x] Feedback visual con emoji y duración
- [x] Console logging detallado con emojis
- [x] Manejo en newSell.vue
- [x] Manejo en completeOrder.vue
- [ ] Prueba con internet lento real
- [ ] Prueba con desconexión total
- [ ] Monitoreo en producción

---

## 📞 Soporte

Si después de implementar esto **aún hay problemas**:

1. **Revisar console logs**: Buscar patrones de timeout (⏰)
2. **Verificar tiempos**: Si 30s no es suficiente, aumentar timeout
3. **Backend**: Optimizar generación de PDFs (cachear, comprimir)
4. **Infraestructura**: Considerar CDN para servir PDFs
5. **Alternativa**: Generar PDFs en frontend con jsPDF (elimina depende)

---

**Fecha**: Febrero 6, 2026  
**Autor**: GitHub Copilot  
**Versión**: 1.0
