# ✅ INSTRUCCIONES PARA ACTIVAR RAPPI Y PEDIDOS YA

## 📋 Resumen de Cambios

Se implementó el mismo comportamiento de Uber Eats para Rappi y Pedidos Ya:
- ✅ Generan boleta SII oficial
- ✅ Se reportan de forma independiente (no se suman a "Efectivo")
- ✅ Almacenan metadata completa del pedido

## 🔧 Cambios Realizados en el Código

### Backend (PHP)
1. ✅ `Server app/app/Http/Controllers/Controllers_local/SellsController.php`
   - Línea 98: Agregado `rappi` y `pedidos_ya` al check de `other_type`
   - Línea 269: Actualizado log y condición para incluir ambas plataformas

2. ✅ `Server app/app/models_local/Sell.php` (y copia en `app/models_local/Sell.php`)
   - Agregado `rappi_payment_info` y `pedidos_ya_payment_info` al array `$fillable`
   - Agregado condiciones para permitir estos campos en `createSell()`

### Frontend (Vue.js)
3. ✅ `src/renderer/components/modals/cafeteria/catalog.vue`
   - Ya tiene botones "Ticket + Rappi (Boleta SII)" y "Ticket + Pedidos Ya (Boleta SII)"

4. ✅ `src/renderer/components/modals/cafeteria/createTicket.vue`
   - Ya detecta `rappi` y `pedidos_ya` por `paymentMethod`
   - Ya envía `rappi_payment_info` y `pedidos_ya_payment_info` como JSON

5. ✅ `src/renderer/components/reports/report.vue`
   - Ya tiene `settingRappi` y `settingPedidosYa` en computed properties
   - Ya muestra totales independientes

## 🗄️ BASE DE DATOS - ACCIÓN REQUERIDA

**IMPORTANTE:** Debes ejecutar el script SQL para agregar las columnas a la base de datos.

### Opción 1: Ejecutar Script SQL Directo (RECOMENDADO)
```bash
# Ejecuta este archivo SQL en tu base de datos:
add_delivery_platforms_columns.sql
```

### Opción 2: Ejecutar Migración de Laravel
```bash
cd "Server app"
php artisan migrate --path=database/migrations/local_migrations/sii_module/2024_11_14_000000_add_delivery_platforms_info_to_sells.php
```

## 📊 Columnas que se Agregan

La tabla `sells` recibirá estas nuevas columnas:
- `uber_payment_info` (TEXT NULL) - Información de pedidos Uber
- `rappi_payment_info` (TEXT NULL) - Información de pedidos Rappi  
- `pedidos_ya_payment_info` (TEXT NULL) - Información de pedidos Pedidos Ya
- `special_payment_info` (TEXT NULL) - Información de otros métodos especiales

Y se actualiza el ENUM `other_type` para incluir:
- `pedidos_ya`
- `uber_eats` (además del `uber` existente)
- `pluxee`
- `banco_chile_20`
- `fagotto_10`
- `halloween_20`

## ✅ Verificación Post-Implementación

Después de ejecutar el SQL:

1. **Prueba con Rappi:**
   - Crear venta con botón "Ticket + Rappi (Boleta SII)"
   - Monto: $200
   - **Esperado en reporte:**
     - Rappi: $200 ✅
     - Efectivo: $0 ✅

2. **Prueba con Pedidos Ya:**
   - Crear venta con botón "Ticket + Pedidos Ya (Boleta SII)"
   - Monto: $300
   - **Esperado en reporte:**
     - Pedidos Ya: $300 ✅
     - Efectivo: $0 ✅

3. **Verificar boleta SII:**
   - Ambos métodos deben generar boleta válida en el SII
   - La boleta debe tener `type_sell = 'boleta'`

## 🔍 Troubleshooting

Si después de ejecutar el SQL las ventas siguen apareciendo en "Efectivo":

1. Verifica que las columnas se crearon:
   ```sql
   SHOW COLUMNS FROM sells LIKE '%payment_info';
   ```

2. Verifica el ENUM:
   ```sql
   SHOW COLUMNS FROM sells WHERE Field = 'other_type';
   ```

3. Revisa los logs de Laravel:
   ```bash
   tail -f "Server app/storage/logs/laravel.log"
   ```
   Deberías ver: `🚀 FIX PAYMENT - Ajustando paymode para reportes`

4. Verifica en la base de datos una venta de prueba:
   ```sql
   SELECT id, total, other_type, paymode, rappi_payment_info 
   FROM sells 
   ORDER BY id DESC 
   LIMIT 1;
   ```
   - `other_type` debe ser `'rappi'` o `'pedidos_ya'`
   - `paymode` debe ser `'rappi'` o `'pedidos_ya'` (NO `'boleta'`)
   - `rappi_payment_info` o `pedidos_ya_payment_info` debe contener el JSON

## 📞 Soporte

Si hay algún problema después de ejecutar el SQL, revisa:
- Los logs del navegador (F12 → Console)
- Los logs de Laravel (`Server app/storage/logs/laravel.log`)
- La última venta en la base de datos

---

**Última actualización:** 14 de noviembre de 2024
**Desarrollado para:** Fagotto Agustinas
