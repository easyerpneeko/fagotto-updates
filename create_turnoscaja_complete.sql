CREATE TABLE IF NOT EXISTS TurnosCaja (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    app_id INT(11) NOT NULL,
    usuario_id INT(11) NOT NULL,
    usuario_nombre VARCHAR(255) NOT NULL,
    app_nombre VARCHAR(255) NOT NULL,
    monto_inicial DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    monto_final DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    fecha_inicio DATETIME NOT NULL,
    fecha_termino DATETIME NULL,
    total_sistema DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_contado DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_otros_medios DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    diferencia DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    diferencia_general DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    estado ENUM('abierto','cerrado','perfecto','sobrante','faltante') NOT NULL DEFAULT 'abierto',
    observaciones TEXT NULL,
    detalle_efectivo LONGTEXT NULL,
    detalle_medios_pago LONGTEXT NULL,
    numero_transacciones INT(11) NOT NULL DEFAULT 0,
    turno_abierto TINYINT(1) NOT NULL DEFAULT 1,
    turno_cerrado_en DATETIME NULL,
    puede_hacer_arqueo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX idx_app_id (app_id),
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_app_usuario (app_id, usuario_id),
    INDEX idx_turno_abierto (turno_abierto),
    INDEX idx_fecha_inicio (fecha_inicio),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SHOW TABLES LIKE 'TurnosCaja';

DESC TurnosCaja;

SELECT 'TABLA TurnosCaja CREADA EXITOSAMENTE' AS mensaje;