# 🚀 QUICK START: Cambiar APPID por Local

## ⚡ Comando Rápido

```powershell
# Windows PowerShell
.\cambiar-local.ps1 [CODIGO_LOCAL]

# Ejemplos:
.\cambiar-local.ps1 MAN001    # Manuel Montt
.\cambiar-local.ps1 ENC001    # Encomenderos
.\cambiar-local.ps1 AGU001    # Agustinas
```

```bash
# Linux / Mac
./cambiar-local.sh [CODIGO_LOCAL]

# Ejemplos:
./cambiar-local.sh MAN001     # Manuel Montt
./cambiar-local.sh ENC001     # Encomenderos
./cambiar-local.sh AGU001     # Agustinas
```

## 🏢 Códigos de Locales

| Código | Local            |
|--------|------------------|
| AGU001 | Agustinas        |
| MAN001 | Manuel Montt     |
| ENC001 | Encomenderos     |

## ✅ Verificar que Funciona

1. Abre la aplicación
2. Presiona F12 (consola)
3. Genera un QR de registro
4. Busca este mensaje:

```
📍 Generando QR para local: { appId: 'MAN001', localNombre: 'Manuel Montt' }
```

5. El link debe contener el APPID correcto:

```
https://...register-employee.php?session=REG123&appid=MAN001
                                                        ^^^^^^
                                                    Debe coincidir
```

## 🆘 Troubleshooting

### ❌ "No se puede ejecutar scripts"

**Windows**:
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

**Linux/Mac**:
```bash
chmod +x cambiar-local.sh
```

### ❌ Link sigue usando AGU001

1. Cierra la aplicación completamente
2. Ejecuta el script de nuevo
3. Abre la aplicación
4. Verifica en consola (F12)

---

**Documentación completa**: [CONFIGURACION_LOCALES.md](CONFIGURACION_LOCALES.md)
