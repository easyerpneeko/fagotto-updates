# ESTRUCTURA DE TABLA: TurnosCaja

## 📋 Estructura Recomendada

```sql
CREATE TABLE TurnosCaja (
    id INT PRIMARY KEY AUTO_INCREMENT,
    app_id INT NOT NULL,
    
    -- INFORMACIÓN DEL TURNO
    fecha_inicio DATETIME NOT NULL,
    fecha_termino DATETIME NOT NULL,
    duracion_minutos INT GENERATED ALWAYS AS (TIMESTAMPDIFF(MINUTE, fecha_inicio, fecha_termino)) STORED,
    
    -- INFORMACIÓN DEL USUARIO
    usuario_id INT NOT NULL,
    usuario_nombre VARCHAR(255) NOT NULL,
    
    -- TOTALES GENERALES
    total_contado DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_sistema DECIMAL(15,2) NOT NULL DEFAULT 0,
    diferencia_total DECIMAL(15,2) GENERATED ALWAYS AS (total_contado - total_sistema) STORED,
    
    -- DETALLE POR MÉTODO DE PAGO (JSON)
    detalle_efectivo JSON NOT NULL,
    detalle_medios_pago JSON NOT NULL,
    
    -- RESUMEN ESTADÍSTICO
    numero_transacciones INT NOT NULL DEFAULT 0,
    metodos_con_diferencia TEXT NULL,
    
    -- OBSERVACIONES Y ESTADO
    observaciones TEXT NULL,
    estado ENUM('completado', 'con_diferencias', 'perfecto') NOT NULL DEFAULT 'completado',
    
    -- TIMESTAMPS
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- ÍNDICES
    INDEX idx_app_fecha (app_id, fecha_termino),
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado),
    
    -- CONSTRAINT
    FOREIGN KEY (app_id) REFERENCES apps(id) ON DELETE CASCADE
);
```

## 🔧 Ejemplo de Datos que se Guardarían

### Campo `detalle_efectivo`:
```json
{
    "denominaciones": {
        "bill_20000": {"cantidad": 5, "valor": 20000, "total": 100000},
        "bill_10000": {"cantidad": 3, "valor": 10000, "total": 30000},
        "bill_5000": {"cantidad": 2, "valor": 5000, "total": 10000},
        "coin_500": {"cantidad": 10, "valor": 500, "total": 5000}
    },
    "total_contado": 145000,
    "total_sistema": 142500,
    "diferencia": 2500
}
```

### Campo `detalle_medios_pago`:
```json
{
    "debito": {"contado": 85000, "sistema": 87000, "diferencia": -2000},
    "credito": {"contado": 45000, "sistema": 43000, "diferencia": 2000},
    "transferencia": {"contado": 25000, "sistema": 25000, "diferencia": 0},
    "rappi": {"contado": 0, "sistema": 15000, "diferencia": -15000},
    "uber": {"contado": 12000, "sistema": 12000, "diferencia": 0},
    "sodexo": {"contado": 8000, "sistema": 8000, "diferencia": 0}
}
```

## 📊 Campos Explicados

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `app_id` | INT | ID de la aplicación/negocio |
| `fecha_inicio` | DATETIME | Cuando empezó el arqueo |
| `fecha_termino` | DATETIME | Cuando terminó el arqueo |
| `duracion_minutos` | INT | Cuánto se demoró (auto-calculado) |
| `usuario_id` | INT | ID del usuario que hizo el arqueo |
| `usuario_nombre` | VARCHAR | Nombre del usuario (por si se borra el usuario) |
| `total_contado` | DECIMAL | Todo lo que contó manualmente |
| `total_sistema` | DECIMAL | Todo lo que registró el sistema |
| `diferencia_total` | DECIMAL | Diferencia total (auto-calculado) |
| `detalle_efectivo` | JSON | Desglose completo del efectivo |
| `detalle_medios_pago` | JSON | Desglose de todos los métodos |
| `numero_transacciones` | INT | Cuántas ventas tuvo el día |
| `metodos_con_diferencia` | TEXT | Lista de métodos con diferencias |
| `observaciones` | TEXT | Comentarios del usuario |
| `estado` | ENUM | 'perfecto', 'con_diferencias', 'completado' |

## 🎯 Ventajas de esta Estructura

1. **📈 Histórico Completo**: Cada turno queda registrado
2. **🔍 Análisis Detallado**: JSON permite consultas específicas
3. **⚡ Performance**: Campos calculados automáticamente
4. **📊 Reportes**: Fácil generar estadísticas
5. **🏢 Multi-tenant**: Separado por `app_id`
6. **🕐 Tiempo Real**: Saber cuánto se demora cada arqueo

## 🚀 Consultas Útiles

```sql
-- Arqueos de hoy
SELECT * FROM TurnosCaja WHERE app_id = 1 AND DATE(fecha_termino) = CURDATE();

-- Promedio de duración
SELECT AVG(duracion_minutos) FROM TurnosCaja WHERE app_id = 1;

-- Métodos con más diferencias
SELECT detalle_medios_pago FROM TurnosCaja WHERE estado = 'con_diferencias';
```

¿Te parece bien esta estructura? ¿Quieres que ajuste algo antes de que la crees?
