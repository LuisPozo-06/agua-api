<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'nombre',
        'telefono',
        'direccion'
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
}