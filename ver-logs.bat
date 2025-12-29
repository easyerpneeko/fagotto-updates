@echo off
echo ========================================
echo   LOGS DE REGISTRO DE EMPLEADOS
echo ========================================
echo.

if exist error_register.log (
    echo Mostrando ultimas 50 lineas del log:
    echo.
    powershell -Command "Get-Content -Path 'error_register.log' -Tail 50"
) else (
    echo No se encontro el archivo de log: error_register.log
    echo.
    echo El archivo se creara cuando ocurra el primer error.
)

echo.
echo ========================================
echo Presiona cualquier tecla para salir...
pause > nul
