# 🏢 ARQUITECTURA DE BASE DE DATOS CENTRALIZADA

## 📊 CONCEPTO

```
┌─────────────────────────────────────────────────────────┐
│         SERVIDOR CENTRAL (fagottoerp.cl)               │
│                                                         │
│     ┌─────────────────────────────────┐               │
│     │  MySQL Database: "asistencias"  │               │
│     │                                 │               │
│     │  ├─ locales                    │               │
│     │  │  ├─ AGU001 (Agustinas)     │               │
│     │  │  ├─ CON001 (Las Condes)    │               │
│     │  │  ├─ PRO001 (Providencia)   │               │
│     │  │  └─ MAI001 (Maipú)         │               │
│     │  │                             │               │
│     │  ├─ employees (global)         │               │
│     │  ├─ checkin_sessions           │               │
│     │  └─ attendance_records         │               │
│     │     (todos los locales aquí)   │               │
│     └─────────────────────────────────┘               │
│                        ▲                               │
└────────────────────────┼───────────────────────────────┘
                         │
          ┌──────────────┼──────────────┐
          │              │              │
          │              │              │
┌─────────▼────┐  ┌──────▼──────┐  ┌───▼──────────┐
│ PC Agustinas │  │ PC Las Condes│  │ PC Providencia│
│              │  │              │  │              │
│ app.exe      │  │ app.exe      │  │ app.exe      │
│ APPID:AGU001 │  │ APPID:CON001 │  │ APPID:PRO001 │
│              │  │              │  │              │
│ QR Generator │  │ QR Generator │  │ QR Generator │
└──────────────┘  └──────────────┘  └──────────────┘
```

---

## 🎯 VENTAJAS DE DB CENTRALIZADA

### ✅ **1. Control Total**
- Un solo lugar para administrar todos los locales
- Reportes globales en tiempo real
- Backup centralizado

### ✅ **2. Sincronización Automática**
- No hay conflictos entre locales
- Datos siempre actualizados
- Un empleado puede marcar en cualquier local

### ✅ **3. Administración Simplificada**
- Agregar/quitar empleados desde cualquier local
- Configurar locales desde una sola interfaz
- Monitoreo centralizado

### ✅ **4. Escalabilidad**
- Agregar nuevos locales es solo cambiar APPID
- No necesitas DB nueva por cada local
- Sistema crece sin complicaciones

---

## 🔧 CONFIGURACIÓN POR LOCAL

### **Local: Agustinas (AGU001)**
```json
// src/renderer/config/app-config.json
{
  "appId": "AGU001",
  "localNombre": "Agustinas",
  "database": {
    "host": "fagottoerp.cl",
    "port": 3306,
    "database": "asistencias",
    "user": "asistencia_user",
    "password": "Fagotto2025!"
  },
  "gps": {
    "latitud": -33.4372,
    "longitud": -70.6506,
    "radio": 50
  }
}
```

### **Local: Las Condes (CON001)**
```json
// src/renderer/config/app-config.json
{
  "appId": "CON001",
  "localNombre": "Las Condes",
  "database": {
    "host": "fagottoerp.cl",  // ← MISMO SERVIDOR
    "port": 3306,
    "database": "asistencias", // ← MISMA DB
    "user": "asistencia_user",
    "password": "Fagotto2025!"
  },
  "gps": {
    "latitud": -33.4150,
    "longitud": -70.5843,
    "radio": 50
  }
}
```

---

## 🔄 FLUJO DE MARCADO

```
1. PC de Agustinas (APPID: AGU001)
   └─ Genera QR: fagotto.cl/qrcheck.php?session=ABC123&appid=AGU001
   └─ Inserta en DB centralizada:
      INSERT INTO checkin_sessions (session_id, app_id, expires_at)

2. Empleado escanea QR desde su celular
   └─ Abre fagotto.cl/qrcheck.php
   └─ Lee app_id del QR (AGU001)
   └─ Muestra: "📍 Agustinas - Registro de Asistencia"

3. Captura foto + AWS Rekognition
   └─ Valida rostro
   └─ Inserta en DB centralizada:
      INSERT INTO attendance_records 
      (app_id='AGU001', nombre_local='Agustinas', employee_id=..., fecha_hora=...)

4. Registro guardado en DB maestra
   └─ Visible desde cualquier local
   └─ Reportes centralizados
```

---

## 📋 INSTALACIÓN POR LOCAL

### **PASO 1: Instalar app.exe en cada PC**
```bash
# Local Agustinas
1. Instalar: fagotto-asistencia-setup.exe
2. Abrir app
3. Ir a: Configuración → Datos del Local
4. Completar:
   - APPID: AGU001
   - Nombre: Agustinas
   - GPS: -33.4372, -70.6506
5. Guardar
```

### **PASO 2: Verificar conexión a DB**
```bash
# La app debe conectarse a:
Host: fagottoerp.cl
DB: asistencias
User: asistencia_user

# NO a localhost
```

### **PASO 3: Probar QR**
```bash
1. Abrir "Registro Asistencia"
2. Ver QR generado
3. Escanear con celular
4. Debe mostrar nombre del local correcto
```

---

## 🗄️ ESTRUCTURA DE TABLAS

### **locales** (Catálogo de Sucursales)
```sql
┌─────────┬────────────┬──────────┬──────────┬───────────┐
│ app_id  │ nombre     │ latitud  │ longitud │ active    │
├─────────┼────────────┼──────────┼──────────┼───────────┤
│ AGU001  │ Agustinas  │ -33.4372 │ -70.6506 │ 1         │
│ CON001  │ Las Condes │ -33.4150 │ -70.5843 │ 1         │
│ PRO001  │ Providencia│ -33.4250 │ -70.6100 │ 1         │
└─────────┴────────────┴──────────┴──────────┴───────────┘
```

### **employees** (Empleados Globales)
```sql
┌────────┬─────────────────┬──────────┬─────────┐
│ id     │ nombre          │ cargo    │ active  │
├────────┼─────────────────┼──────────┼─────────┤
│ EMP001 │ Juan Pérez      │ Cajero   │ 1       │
│ EMP002 │ María González  │ Supervisor│ 1      │
└────────┴─────────────────┴──────────┴─────────┘
```

### **attendance_records** (Registros de Todos los Locales)
```sql
┌────┬─────────┬──────────────┬────────┬────────────────┬───────────┐
│ id │ app_id  │ nombre_local │ emp_id │ nombre         │ fecha_hora│
├────┼─────────┼──────────────┼────────┼────────────────┼───────────┤
│ 1  │ AGU001  │ Agustinas    │ EMP001 │ Juan Pérez     │ 08:00:23  │
│ 2  │ CON001  │ Las Condes   │ EMP002 │ María González │ 08:09:15  │
│ 3  │ AGU001  │ Agustinas    │ EMP003 │ Pedro Sánchez  │ 08:15:45  │
└────┴─────────┴──────────────┴────────┴────────────────┴───────────┘
```

---

## 📊 CONSULTAS ÚTILES

### **Ver asistencia de hoy (todos los locales)**
```sql
SELECT 
  app_id,
  nombre_local,
  nombre,
  DATE_FORMAT(fecha_hora, '%H:%i') as hora
FROM attendance_records
WHERE DATE(fecha_hora) = CURDATE()
ORDER BY fecha_hora DESC;
```

### **Ver asistencia de un local específico**
```sql
SELECT * 
FROM attendance_records
WHERE app_id = 'AGU001'
AND DATE(fecha_hora) = CURDATE();
```

### **Resumen por local (hoy)**
```sql
SELECT 
  app_id,
  nombre_local,
  COUNT(*) as total_marcas,
  COUNT(DISTINCT employee_id) as empleados_distintos,
  MIN(DATE_FORMAT(fecha_hora, '%H:%i')) as primera_entrada,
  MAX(DATE_FORMAT(fecha_hora, '%H:%i')) as ultima_entrada
FROM attendance_records
WHERE DATE(fecha_hora) = CURDATE()
GROUP BY app_id, nombre_local;
```

### **Empleado que marcó en múltiples locales**
```sql
SELECT 
  employee_id,
  nombre,
  COUNT(DISTINCT app_id) as locales_visitados,
  GROUP_CONCAT(DISTINCT nombre_local) as locales
FROM attendance_records
WHERE DATE(fecha_hora) = CURDATE()
GROUP BY employee_id, nombre
HAVING COUNT(DISTINCT app_id) > 1;
```

---

## 🔐 SEGURIDAD

### **1. Usuario de BD con permisos limitados**
```sql
-- NO usar root en producción
CREATE USER 'asistencia_user'@'%' IDENTIFIED BY 'Fagotto2025!';
GRANT SELECT, INSERT, UPDATE ON asistencias.* TO 'asistencia_user'@'%';
FLUSH PRIVILEGES;
```

### **2. Firewall MySQL**
```bash
# Solo permitir conexiones desde IPs conocidas
# Configurar en servidor MySQL
```

### **3. SSL/TLS para conexión**
```json
{
  "database": {
    "host": "fagottoerp.cl",
    "ssl": true,
    "sslCert": "/path/to/cert.pem"
  }
}
```

---

## 🚀 DEPLOYMENT

### **Servidor Central (Una sola vez)**
```bash
1. Crear DB "asistencias"
2. Ejecutar schema_asistencia.sql
3. Ejecutar add_locales_table.sql
4. Crear usuario asistencia_user
5. Configurar acceso remoto
6. Habilitar puerto 3306 en firewall
```

### **Cada Local (Repetir por sucursal)**
```bash
1. Instalar app.exe
2. Configurar APPID único
3. Probar conexión a DB remota
4. Verificar GPS del local
5. Generar QR de prueba
```

---

## ✅ CHECKLIST DE IMPLEMENTACIÓN

### **Servidor Central**
- [ ] Crear DB "asistencias"
- [ ] Ejecutar scripts SQL
- [ ] Crear usuario asistencia_user
- [ ] Permitir conexiones remotas
- [ ] Backup automático configurado

### **Local Agustinas (AGU001)**
- [ ] App instalada
- [ ] APPID: AGU001 configurado
- [ ] Conexión a DB remota OK
- [ ] GPS correcto
- [ ] QR probado

### **Local Las Condes (CON001)**
- [ ] App instalada
- [ ] APPID: CON001 configurado
- [ ] Conexión a DB remota OK
- [ ] GPS correcto
- [ ] QR probado

### **Local Providencia (PRO001)**
- [ ] App instalada
- [ ] APPID: PRO001 configurado
- [ ] Conexión a DB remota OK
- [ ] GPS correcto
- [ ] QR probado

---

## 📈 ESCALABILIDAD

### **Agregar nuevo local:**
```sql
-- 1. Insertar en tabla locales
INSERT INTO locales (app_id, nombre, direccion, latitud, longitud)
VALUES ('MAI001', 'Maipú', 'Pajaritos 3456', -33.5088, -70.7644);

-- 2. Instalar app.exe en PC del nuevo local
-- 3. Configurar APPID: MAI001
-- 4. Listo! Ya está integrado al sistema
```

### **Sin límite de locales:**
- Cada local solo necesita APPID único
- Todos escriben en misma DB
- No hay sincronización manual
- Sistema crece automáticamente

---

## 🎯 PRÓXIMOS PASOS

1. ✅ Crear DB "asistencias" en servidor central
2. ✅ Ejecutar scripts SQL
3. ✅ Configurar acceso remoto MySQL
4. ✅ Actualizar app.exe con lector de app-config.json
5. ✅ Probar conexión desde PC Agustinas
6. ✅ Probar conexión desde PC Las Condes
7. ✅ Verificar que ambos escriben en misma DB

**¿Todo claro? Sistema mucho más profesional así.**
