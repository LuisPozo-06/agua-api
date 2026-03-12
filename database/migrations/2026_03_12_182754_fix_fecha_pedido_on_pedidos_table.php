<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->timestamp('fecha_pedido')->useCurrent()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // Revertir no es trivial sin saber exactamente el estado anterior, 
            // pero podemos dejarlo como estaba si es necesario.
            // Generalmente, solo dejarlo como timestamp es el default.
            $table->timestamp('fecha_pedido')->change();
        });
    }
};
