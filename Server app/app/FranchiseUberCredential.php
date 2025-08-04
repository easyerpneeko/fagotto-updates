<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class FranchiseUberCredential extends Model
{
    protected $table = 'franchise_uber_credentials';
    
    protected $fillable = [
        'franchise_id',
        'franchise_name',
        'uber_client_id',
        'uber_client_secret',
        'uber_store_id',
        'webhook_url',
        'prep_time_minutes',
        'auto_accept_orders',
        'is_active',
        'is_configured',
        'last_sync_at',
        'last_test_result',
        'contact_email',
        'contact_phone'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_configured' => 'boolean',
        'auto_accept_orders' => 'boolean',
        'last_sync_at' => 'datetime',
        'last_test_result' => 'array',
        'prep_time_minutes' => 'integer'
    ];

    protected $hidden = [
        'uber_client_secret' // No exponer en JSON por seguridad
    ];

    /**
     * Encriptar client_id al guardar
     */
    public function setUberClientIdAttribute($value)
    {
        $this->attributes['uber_client_id'] = Crypt::encryptString($value);
    }

    /**
     * Desencriptar client_id al leer
     */
    public function getUberClientIdAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Encriptar client_secret al guardar
     */
    public function setUberClientSecretAttribute($value)
    {
        $this->attributes['uber_client_secret'] = Crypt::encryptString($value);
    }

    /**
     * Desencriptar client_secret al leer
     */
    public function getUberClientSecretAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Encriptar store_id al guardar
     */
    public function setUberStoreIdAttribute($value)
    {
        $this->attributes['uber_store_id'] = Crypt::encryptString($value);
    }

    /**
     * Desencriptar store_id al leer
     */
    public function getUberStoreIdAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Scope para franquicias activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para franquicias configuradas
     */
    public function scopeConfigured($query)
    {
        return $query->where('is_configured', true);
    }

    /**
     * Verificar si las credenciales están completas
     */
    public function hasCompleteCredentials()
    {
        return !empty($this->uber_client_id) && 
               !empty($this->uber_client_secret) && 
               !empty($this->uber_store_id);
    }

    /**
     * Marcar como configurada
     */
    public function markAsConfigured()
    {
        $this->update([
            'is_configured' => true,
            'last_sync_at' => now()
        ]);
    }

    /**
     * Guardar resultado de prueba de conexión
     */
    public function saveTestResult($success, $message = null, $data = null)
    {
        $this->update([
            'last_test_result' => [
                'success' => $success,
                'message' => $message,
                'data' => $data,
                'tested_at' => now()->toISOString()
            ],
            'last_sync_at' => now()
        ]);
    }

    /**
     * Obtener estado de la última prueba
     */
    public function getLastTestStatus()
    {
        if (!$this->last_test_result) {
            return 'never_tested';
        }

        return $this->last_test_result['success'] ? 'success' : 'failed';
    }

    /**
     * Relación con pedidos de Uber Eats
     */
    public function uberOrders()
    {
        return $this->hasMany(UberEatsOrder::class, 'franchise_id', 'franchise_id');
    }
}
