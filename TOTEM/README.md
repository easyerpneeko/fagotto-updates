# ?? Mercado Pago Point Smart - Sistema de Pagos

Sistema integrado para enviar pagos al terminal Point Smart de Mercado Pago desde web.

## ?? Configuración

**Terminal:**
- Modelo: NEWLAND N950
- S/N: N950NCC302980808
- Modo: PDV ?
- País: Chile (CLP)

## ?? Archivos principales

### 1. enviar-pago-web.php
Interfaz web para crear y enviar pagos al terminal.
URL: http://localhost/mercadopago-point-example/enviar-pago-web.php

### 2. validar-pago.php
Consultar el estado de un pago en tiempo real con auto-refresh.
URL: http://localhost/mercadopago-point-example/validar-pago.php

### 3. webhook-point.php
Recibe notificaciones automáticas de Mercado Pago.

### 4. ver-mi-terminal.php
Consultar información del terminal vinculado.

## ?? Flujo de trabajo

1. Crear pago: enviar-pago-web.php ? Ingresar monto ? Copiar Order ID
2. Validar: validar-pago.php ? Pegar Order ID ? Activar auto-refresh
3. Completar: Pasar tarjeta en Point Smart ? Estado cambia a APROBADO

## ?? Estados de pago

- created - Esperando pago
- approved - Aprobado ?
- rejected - Rechazado ?
- cancelled - Cancelado ??

## ?? URLs Producción

- Enviar: https://fagottoerp.cl/mercadopago/enviar-pago-web.php
- Validar: https://fagottoerp.cl/mercadopago/validar-pago.php
- Webhook: https://fagottoerp.cl/mercadopago/webhook-point.php
