#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Generador de INSERT para folios de guía de despacho
Este script genera un archivo SQL con todos los INSERT necesarios
"""

# Configuración
folio_inicial = 501
folio_final = 3500
xml_id = 'REEMPLAZAR_CON_ID'  # Se reemplaza manualmente después de ejecutar el primer script

# Crear el archivo SQL
output_file = '2_CREAR_FOLIOS_INDIVIDUALES_52.sql'

with open(output_file, 'w', encoding='utf-8') as f:
    # Encabezado
    f.write("""-- =========================================
-- CREAR FOLIOS INDIVIDUALES - GUÍA DE DESPACHO
-- =========================================
-- Este archivo crea todos los folios del 501 al 3500
-- IMPORTANTE: Reemplaza [XML_ID] con el ID real del xml_cargados
-- que obtuviste del script anterior
-- =========================================

-- Primero, obtén el ID del XML insertado:
-- SELECT id FROM xml_cargados WHERE type = 'guia_de_despacho' ORDER BY id DESC LIMIT 1;

-- Reemplaza [XML_ID] a continuación con el ID real

""")
    
    # Crear bloques de 100 folios
    total_folios = folio_final - folio_inicial + 1
    bloques = (total_folios // 100) + (1 if total_folios % 100 != 0 else 0)
    
    for bloque in range(bloques):
        inicio_bloque = folio_inicial + (bloque * 100)
        fin_bloque = min(inicio_bloque + 99, folio_final)
        
        f.write(f"\n-- Bloque {bloque + 1} de {bloques}: Folios {inicio_bloque} a {fin_bloque}\n")
        f.write("INSERT INTO folios (folio, type, xml_id, trash, created_at, updated_at) VALUES\n")
        
        valores = []
        for folio in range(inicio_bloque, fin_bloque + 1):
            valores.append(f"({folio}, 'guia_de_despacho', [XML_ID], 0, NOW(), NOW())")
        
        f.write(",\n".join(valores))
        f.write(";\n")
    
    # Verificación final
    f.write("""
-- =========================================
-- VERIFICACIÓN FINAL
-- =========================================

-- Contar folios creados
SELECT 
    'Total folios creados' as descripcion,
    COUNT(*) as cantidad,
    MIN(folio) as folio_minimo,
    MAX(folio) as folio_maximo
FROM folios 
WHERE xml_id = [XML_ID] AND type = 'guia_de_despacho';

-- Ver los primeros 10 folios
SELECT * FROM folios 
WHERE xml_id = [XML_ID] AND type = 'guia_de_despacho'
ORDER BY folio ASC 
LIMIT 10;

-- Ver folios disponibles
SELECT COUNT(*) as folios_disponibles
FROM folios 
WHERE xml_id = [XML_ID] 
AND type = 'guia_de_despacho' 
AND guia_id IS NULL 
AND trash = 0;

-- =========================================
-- ¡LISTO! Deberías tener 2,999 folios creados
-- =========================================
""")

print(f"✅ Archivo generado: {output_file}")
print(f"📊 Total de folios a crear: {total_folios}")
print(f"📦 Dividido en {bloques} bloques de hasta 100 folios cada uno")
print("\n🔥 INSTRUCCIONES:")
print("1. Ejecuta primero: 1_LIMPIAR_E_INSERTAR_XML.sql")
print("2. Anota el ID del xml_cargados que se creó")
print(f"3. Abre {output_file} y reemplaza [XML_ID] con el ID real")
print("4. Ejecuta el archivo modificado")
