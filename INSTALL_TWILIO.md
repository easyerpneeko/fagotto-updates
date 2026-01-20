# Instalación de Twilio SDK

## En el servidor (posfagotto.cl)

Conectarte por SSH y ejecutar:

```bash
cd /path/to/your/laravel/project
composer require twilio/sdk
```

## O si no tienes acceso SSH

Agrega esta línea al archivo `composer.json` en la sección "require":

```json
"require": {
    "twilio/sdk": "^7.0"
}
```

Y luego ejecuta:
```bash
composer install
```
