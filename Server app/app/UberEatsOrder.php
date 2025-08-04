<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UberEatsOrder extends Model
{
    protected $table = 'uber_eats_orders';
    
    protected $fillable = [
        'uber_order_id',
        'display_id',
        'external_reference_id',
        'status',
        'order_type',
        'brand',
        'store_id',
        'franchise_id', // Nuevo campo para relacionar con franquicia
        'eater_id',
        'customer_name',
        'customer_phone',
        'delivery_address',
        'delivery_instructions',
        'order_data',
        'total_amount',
        'currency',
        'placed_at',
        'estimated_ready_for_pickup_at',
        'accepted_at',
        'ready_at',
        'picked_up_at',
        'delivered_at',
        'cancelled_at',
        'processed_in_pos',
        'pos_ticket_id',
        'notes'
    ];

    protected $casts = [
        'order_data' => 'array',
        'placed_at' => 'datetime',
        'estimated_ready_for_pickup_at' => 'datetime',
        'accepted_at' => 'datetime',
        'ready_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'processed_in_pos' => 'boolean',
        'total_amount' => 'decimal:2'
    ];

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['delivered', 'cancelled']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'created');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Accessors
     */
    public function getItemsAttribute()
    {
        return $this->order_data['items'] ?? [];
    }

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total_amount, 0, ',', '.');
    }

    public function getStatusTextAttribute()
    {
        $statuses = [
            'created' => 'Nuevo',
            'accepted' => 'Aceptado',
            'in_progress' => 'En preparación',
            'ready_for_pickup' => 'Listo para recoger',
            'picked_up' => 'Recogido',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getDeliveryInfoAttribute()
    {
        return [
            'address' => $this->delivery_address,
            'instructions' => $this->delivery_instructions
        ];
    }

    /**
     * Methods
     */
    public function markAsAccepted($readyTime = null)
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'estimated_ready_for_pickup_at' => $readyTime ? \Carbon\Carbon::parse($readyTime) : now()->addMinutes(15)
        ]);
    }

    public function markAsInProgress()
    {
        $this->update([
            'status' => 'in_progress'
        ]);
    }

    public function markAsReady()
    {
        $this->update([
            'status' => 'ready_for_pickup',
            'ready_at' => now()
        ]);
    }

    public function markAsPickedUp()
    {
        $this->update([
            'status' => 'picked_up',
            'picked_up_at' => now()
        ]);
    }

    public function markAsDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now()
        ]);
    }

    public function markAsCancelled()
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now()
        ]);
    }

    public function markAsProcessedInPOS($ticketId = null)
    {
        $this->update([
            'processed_in_pos' => true,
            'pos_ticket_id' => $ticketId
        ]);
    }

    /**
     * Create order from Uber API data
     */
    public static function createFromUberData($uberOrderData)
    {
        // Extraer información del cliente
        $customer = $uberOrderData['eater'] ?? [];
        $customerName = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));
        
        // Extraer información de entrega
        $delivery = $uberOrderData['delivery'] ?? [];
        $location = $delivery['location'] ?? [];
        
        return self::create([
            'uber_order_id' => $uberOrderData['id'],
            'display_id' => $uberOrderData['display_id'] ?? $uberOrderData['id'],
            'external_reference_id' => $uberOrderData['external_reference_id'] ?? null,
            'status' => $uberOrderData['current_state'] ?? 'created',
            'order_type' => $uberOrderData['type'] ?? 'delivery',
            'brand' => $uberOrderData['brand'] ?? '',
            'store_id' => $uberOrderData['store']['id'] ?? '',
            'eater_id' => $customer['id'] ?? '',
            'customer_name' => $customerName ?: 'Cliente Uber Eats',
            'customer_phone' => $customer['phone_number'] ?? '',
            'delivery_address' => $location['address'] ?? '',
            'delivery_instructions' => $delivery['notes'] ?? '',
            'order_data' => $uberOrderData,
            'total_amount' => ($uberOrderData['payment']['charges']['total'] ?? 0) / 100, // Convertir centavos a pesos
            'currency' => $uberOrderData['payment']['charges']['currency_code'] ?? 'CLP',
            'placed_at' => isset($uberOrderData['placed_at']) ? \Carbon\Carbon::parse($uberOrderData['placed_at']) : now(),
            'estimated_ready_for_pickup_at' => isset($uberOrderData['estimated_ready_for_pickup_at']) ? 
                \Carbon\Carbon::parse($uberOrderData['estimated_ready_for_pickup_at']) : null,
        ]);
    }

    /**
     * Update order from Uber API data
     */
    public function updateFromUberData($uberOrderData)
    {
        $this->update([
            'status' => $uberOrderData['current_state'] ?? $this->status,
            'order_data' => $uberOrderData,
            'total_amount' => ($uberOrderData['payment']['charges']['total'] ?? 0) / 100,
        ]);
    }

    /**
     * Relación con credenciales de franquicia
     */
    public function franchiseCredentials()
    {
        return $this->belongsTo(FranchiseUberCredential::class, 'franchise_id', 'franchise_id');
    }

    /**
     * Scope para filtrar por franquicia
     */
    public function scopeForFranchise($query, $franchiseId)
    {
        return $query->where('franchise_id', $franchiseId);
    }
}
