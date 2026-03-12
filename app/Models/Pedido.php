<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'cliente_id',
        'cantidad_agua',
        'direccion_entrega',
        'prioridad',
        'estado',
        'estado_updated_at',
        'fecha_pedido',
        'nota',
        'estado_pago',
        'estado_pago_updated_at',
        'comprobante_url',
    ];

    protected $casts = [
        'estado_updated_at'      => 'datetime',
        'estado_pago_updated_at' => 'datetime',
        'fecha_pedido'           => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}