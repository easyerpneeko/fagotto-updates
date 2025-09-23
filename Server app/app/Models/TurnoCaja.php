<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TurnoCaja extends Model
{
    use HasFactory;

    protected $table = 'TurnosCaja';

    protected $fillable = [
        'app_id',
        'usuario_id',
        'fecha_inicio',
        'fecha_termino',
        'total_sistema',
        'total_contado',
        'diferencia',
        'estado',
        'observaciones'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_termino' => 'datetime',
        'total_sistema' => 'decimal:2',
        'total_contado' => 'decimal:2',
        'diferencia' => 'decimal:2'
    ];

    // Relaciones si las necesitas
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Métodos auxiliares
    public function calcularDiferencia()
    {
        $this->diferencia = $this->total_contado - $this->total_sistema;
        return $this;
    }

    public function cerrarTurno()
    {
        $this->fecha_termino = now();
        $this->estado = 'cerrado';
        return $this;
    }
}