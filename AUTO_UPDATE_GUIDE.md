# Auto-Update Setup Guide

## Pasos para publicar una actualización:

### 1. Actualizar la versión en package.json
```json
{
  "version": "1.0.2"
}
```

### 2. Crear un nuevo release en GitHub
Ir a: https://github.com/easyerpneeko/fagotto-updates/releases/new

- **Tag version**: v1.0.2
- **Release title**: Version 1.0.2
- **Descripción**: Describe los cambios realizados

### 3. Construir y publicar la aplicación
```bash
npm run deploy
```

Este comando:
- Construye la aplicación
- Genera los archivos ejecutables
- Los sube automáticamente al release de GitHub

### 4. Verificar archivos en el release
Los siguientes archivos deben estar en el release:
- `fagotto-erd-aplication Setup 1.0.2.exe`
- `fagotto-erd-aplication Setup 1.0.2.exe.blockmap`
- `latest.yml`

### 5. Probar el auto-update
La aplicación debería detectar automáticamente la nueva versión y mostrar una notificación para actualizar.

## Troubleshooting

### Si el auto-update no funciona:
1. Verificar que el token de GitHub tenga permisos correctos
2. Asegurarse de que el repositorio fagotto-updates existe
3. Verificar que los archivos latest.yml estén en el release
4. Revisar los logs en la consola de la aplicación

### Archivos importantes:
- `gh_token.json`: Contiene el token de GitHub (no debe subirse al repo)
- `package.json`: Configuración de publish
- `src/main/index.js`: Lógica del auto-updater
