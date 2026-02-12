#!/bin/bash
# Script para iniciar Project Zomboid Server

cd ~/pzserver

# Iniciar servidor en screen (para poder dejarlo corriendo en background)
screen -S pzserver -dm ./start-server.sh

echo "Servidor iniciado en screen session 'pzserver'"
echo ""
echo "Para ver el servidor: screen -r pzserver"
echo "Para salir de screen sin cerrar el servidor: Ctrl+A, D"
echo "Para detener el servidor: screen -X -S pzserver quit"
