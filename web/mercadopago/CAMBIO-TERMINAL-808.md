📋 CAMBIO TEMPORAL DE TERMINAL - LAS CONDES

Fecha: 2026-02-16
Razón: Terminal 807 bloqueado con HTTP 409 (pago pendiente)
Duración: Temporal hasta limpiar terminal 807

## Cambios Aplicados

### ❌ ANTES (Terminal 807 - PRODUCCIÓN)
```
APP_ID: 116 (Las Condes)
Device ID: NEWLAND_N950__N950NCC302980807
Store ID: 75998370
POS ID: 120882246
Estado: BLOQUEADO - HTTP 409 (pago pendiente)
```

### ✅ AHORA (Terminal 808 - PRUEBAS con Token Cuenta 1)
```
APP_ID: 116 (Las Condes)
Device ID: NEWLAND_N950__N950NCC302980808
Store ID: 76216860
POS ID: 121142594
Access Token: APP_USR-6283355973944660... (CUENTA 1 PRUEBAS)
Estado: ACTIVO - Sin pagos pendientes
```

## ⚠️ IMPORTANTE: Cambio de Token

El terminal 808 pertenece a **Cuenta 1** (Pruebas), NO a Cuenta 2.
Usar el token de Cuenta 2 con terminal 808 genera **HTTP 403 Forbidden**.

Por eso se cambió:
- ❌ Token Cuenta 2: APP_USR-8228397783120956...
- ✅ Token Cuenta 1: APP_USR-6283355973944660...

## Archivos Modificados

1. **web/mercadopago/.env**
   - `MP_DEVICE_ID_116` cambiado de `807` a `808`

2. **web/mercadopago/maquinas-config.php**
   - `device_id` para app_id 116 cambiado a terminal 808
   - `store_id` actualizado a 76216860
   - `pos_id` actualizado a 121142594

3. **web/mercadopago/api-enviar-pago.php**
   - Comentarios actualizados para reflejar el cambio

## Para Subir a fagottoerp.cl

```bash
# Subir archivos actualizados:
/var/www/html/mercadopago/.env
/var/www/html/mercadopago/maquinas-config.php
/var/www/html/mercadopago/api-enviar-pago.php
```

## Verificar Después de Subir

```
https://fagottoerp.cl/mercadopago/test-env-fixed.php
```

Debe mostrar:
- `MP_DEVICE_ID_116`: NEWLAND_N950__N950NCC302980808

```
https://fagottoerp.cl/mercadopago/test-completo-desde-laravel.php
```

Debe mostrar:
- HTTP 201 (éxito) si terminal 808 está libre
- HTTP 409 solo si terminal 808 también tiene pago pendiente

## Probar Desde la App

1. Abre app de Las Condes (app_id 116)
2. Agrega un producto
3. Selecciona "Pago con Tarjeta" → Débito
4. El pago se enviará al terminal 808 (el de Jimmy)
5. Completa el pago en el terminal 808

## IMPORTANTE: Revertir Cuando Se Solucione

Una vez que se limpie el terminal 807 o se resuelva el HTTP 409, revertir a:

```env
MP_DEVICE_ID_116=NEWLAND_N950__N950NCC302980807
```

Store ID: 75998370
POS ID: 120882246

## Notas

- ⚠️ El terminal 808 es el terminal de pruebas de Jimmy
- ✅ Usa el mismo access_token de Cuenta 2 (Las Condes)
- ✅ Solo cambia el device_id, no las credenciales
- ⚠️ Los pagos procesados aparecerán en el dashboard de Cuenta 2 MercadoPago
- ⚠️ Este es un cambio TEMPORAL para testing
