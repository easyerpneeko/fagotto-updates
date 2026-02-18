<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

class PedidoComentario extends Model
{
    protected $connection = 'mysql_local';
    protected $table = 'pedido_comentarios';

    protected $fillable = [
        'request_id',
        'user_name',
        'user_id',
        'comentario'
    ];

    /**
     * Relación con el pedido (Request)
     */
    public function request()
    {
        return $this->belongsTo(Requests::class, 'request_id');
    }

    /**
     * Scope para obtener comentarios ordenados por fecha
     */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('created_at', 'asc');
    }
}
