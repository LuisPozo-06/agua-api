<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chofer extends Model
{
    protected $fillable = [
        'nombres_completos',
        'telefono',
        'is_active',
        'estado_asignacion',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
}
