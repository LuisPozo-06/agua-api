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
        'fecha_pedido'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}