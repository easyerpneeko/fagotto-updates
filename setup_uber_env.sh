#!/bin/bash
# Script para configurar Uber Eats en el servidor

# Agregar las credenciales de Uber Eats al .env
echo "UBER_EATS_CLIENT_ID=EsXBQrBR3Z990kg7MFXINVgURoUMlsiv" >> /var/www/html/server/.env
echo "UBER_EATS_CLIENT_SECRET=H9LcaOrKKIhsO9uCO3AjXClfmWZFmLPpNmPMKzdJ" >> /var/www/html/server/.env
echo "UBER_EATS_REDIRECT_URI=https://posfagotto.cl/api/uber-eats/auth/callback" >> /var/www/html/server/.env

echo "Credenciales de Uber Eats configuradas correctamente"

# Verificar que se agregaron
echo "Verificando configuración:"
cd /var/www/html/server
grep -E "UBER_" .env
