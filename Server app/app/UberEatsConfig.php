<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class UberEatsConfig extends Model
{
    protected $table = 'uber_eats_config';
    
    protected $fillable = [
        'application_id',
        'store_uuid',
        'client_id',
        'client_secret',
        'access_token',
        'token_expires_at',
        'is_active',
        'webhook_url'
    ];

    protected $dates = [
        'token_expires_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relación con la aplicación
    public function application()
    {
        return $this->belongsTo(Aplication::class, 'application_id');
    }

    // Encriptar automáticamente las credenciales sensibles
    public function setClientIdAttribute($value)
    {
        $this->attributes['client_id'] = $value ? Crypt::encrypt($value) : null;
    }

    public function getClientIdAttribute($value)
    {
        return $value ? Crypt::decrypt($value) : null;
    }

    public function setClientSecretAttribute($value)
    {
        $this->attributes['client_secret'] = $value ? Crypt::encrypt($value) : null;
    }

    public function getClientSecretAttribute($value)
    {
        return $value ? Crypt::decrypt($value) : null;
    }

    public function setAccessTokenAttribute($value)
    {
        $this->attributes['access_token'] = $value ? Crypt::encrypt($value) : null;
    }

    public function getAccessTokenAttribute($value)
    {
        return $value ? Crypt::decrypt($value) : null;
    }

    // Verificar si el token está válido
    public function isTokenValid()
    {
        return $this->access_token && 
               $this->token_expires_at && 
               $this->token_expires_at->isFuture();
    }

    // Configuración específica para Fagotto Terminal TurBus
    public static function getFagottoTerminalConfig()
    {
        return self::where('store_uuid', 'e244a540-4071-56ac-875d-c0fc02aed530')->first();
    }
}
