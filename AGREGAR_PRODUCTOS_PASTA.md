# 🍝 Agregar Productos de Pasta a Todos los Negocios

Este conjunto de scripts te permite insertar los 2 nuevos productos de pasta en **todas las bases de datos de los negocios** automáticamente.

## 📋 Productos a Insertar

1. **Pasta Bigoli Boloñesa**
   - Precio: $4,990
   - Costo: $2,990
   - Ganancia: $2,000

2. **Pasta Fettucine Champiñon**
   - Precio: $4,990
   - Costo: $2,990
   - Ganancia: $2,000

## 📁 Archivos Incluidos

- `database/add_productos_pasta_all_locals.sql` - Script SQL base
- `ejecutar_automatico_todos_negocios.ps1` - Script PowerShell (Windows)
- `ejecutar_automatico_todos_negocios.sh` - Script Bash (Linux/Mac)
- `ejecutar_en_todos_negocios.sql` - SQL manual

---

## 🚀 Opción 1: Ejecución Automática (Recomendado)

### Windows (PowerShell):

```powershell
# 1. Editar el script y configurar tu password
notepad ejecutar_automatico_todos_negocios.ps1

# 2. Cambiar esta línea:
$MYSQL_PASSWORD = "tu_password"  # <-- Poner tu password real

# 3. Ajustar ruta de MySQL si es necesaria (por defecto):
$MYSQL_PATH = "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe"

# 4. Ejecutar el script
.\ejecutar_automatico_todos_negocios.ps1
```

### Linux/Mac (Bash):

```bash
# 1. Dar permisos de ejecución
chmod +x ejecutar_automatico_todos_negocios.sh

# 2. Editar y configurar tu password
nano ejecutar_automatico_todos_negocios.sh

# 3. Cambiar esta línea:
MYSQL_PASSWORD="tu_password"  # <-- Poner tu password real

# 4. Ejecutar el script
./ejecutar_automatico_todos_negocios.sh
```

---

## 🔧 Opción 2: Ejecución Manual

### Método A: Un negocio a la vez

```sql
-- Conectar a la base de datos del negocio
USE fagotto_local_1;

-- Ejecutar el script
SOURCE database/add_productos_pasta_all_locals.sql;

-- Verificar
SELECT * FROM products 
WHERE name IN ('Pasta Bigoli Boloñesa', 'Pasta Fettucine Champiñon');
```

### Método B: Usando el script manual

```sql
-- 1. Editar ejecutar_en_todos_negocios.sql
-- 2. Reemplazar los nombres de las bases de datos
-- 3. Ejecutar desde MySQL:

mysql -u root -p < ejecutar_en_todos_negocios.sql
```

---

## ✅ Verificación

El script automáticamente verifica que los productos se hayan insertado correctamente en cada base de datos.

### Verificación Manual:

```sql
-- Ver en todas las bases de datos
SELECT 
    'fagotto_local_1' AS negocio,
    COUNT(*) AS productos
FROM fagotto_local_1.products 
WHERE name IN ('Pasta Bigoli Boloñesa', 'Pasta Fettucine Champiñon')

UNION ALL

SELECT 
    'fagotto_local_2' AS negocio,
    COUNT(*) AS productos
FROM fagotto_local_2.products 
WHERE name IN ('Pasta Bigoli Boloñesa', 'Pasta Fettucine Champiñon');
```

---

## 📝 Notas Importantes

1. **Stock Inicial**: Los productos se insertan con stock = 0. Cada negocio debe ajustar su inventario.

2. **Categorías**: La categoría es NULL por defecto. Cada negocio debe asignar la categoría correcta según su catálogo.

3. **Duplicados**: Si el producto ya existe (mismo nombre), se actualizará en vez de duplicar gracias a `ON DUPLICATE KEY UPDATE`.

4. **Backup**: Recomendable hacer backup antes de ejecutar:
   ```bash
   mysqldump -u root -p --all-databases > backup_antes_productos.sql
   ```

---

## 🔍 Patrón de Bases de Datos

El script busca automáticamente todas las bases de datos que coincidan con:

```
fagotto_local_*
```

**Si tus bases de datos tienen otro patrón**, edita esta línea en el script:

```powershell
# PowerShell
$query = "SHOW DATABASES LIKE 'fagotto_local_%';"

# Bash
DATABASES=$(mysql ... -e "SHOW DATABASES LIKE 'fagotto_local_%';")
```

Cambia `fagotto_local_%` por tu patrón (ej: `negocio_%`, `local_%`, etc.)

---

## 🆘 Solución de Problemas

### Error: "Access denied"
- Verifica usuario y password en el script
- Asegúrate de tener permisos en todas las BDs

### Error: "mysql.exe no encontrado"
- Ajusta la ruta de MySQL en el script
- Agrega MySQL al PATH de Windows

### Error: "SQL syntax error"
- Verifica que el archivo SQL esté en la ruta correcta
- Revisa que no tenga caracteres especiales

### Productos no aparecen
- Verifica que el nombre sea exacto (mayúsculas/minúsculas)
- Revisa el campo `trash` (debe ser 0)
- Verifica que `active` sea 1

---

## 🎯 Resultado Esperado

Al finalizar, verás algo como:

```
======================================================================
📋 RESUMEN DE EJECUCIÓN
======================================================================
✅ Éxitos: 15 bases de datos
❌ Fallos: 0 bases de datos
======================================================================

BaseDatos           ProductosInsertados
---------           -------------------
fagotto_local_1                       2
fagotto_local_2                       2
fagotto_local_3                       2
...
```

---

## 📞 Soporte

Si tienes problemas, verifica:
1. Conexión a MySQL
2. Permisos de usuario
3. Nombres de bases de datos
4. Sintaxis del script SQL
