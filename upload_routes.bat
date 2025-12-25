@echo off
echo Copiando routes/api.php al servidor...
pscp -pw Jim2016@123 "c:\Users\jimmy\Documents\GitHub\FRONT-PROJECT-VUE-DEV\routes\api.php" jimmy@posfagotto.cl:/tmp/api.php
echo.
echo Ahora conectate con PuTTY y ejecuta:
echo sudo mv /tmp/api.php /var/www/html/server/routes/api.php
echo sudo chown www-data:www-data /var/www/html/server/routes/api.php
pause
