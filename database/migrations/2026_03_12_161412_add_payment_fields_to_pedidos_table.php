<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // Estado de pago
            $table->enum('estado_pago', ['Pendiente', 'Pagado'])->default('Pendiente')->after('estado');
            $table->timestamp('estado_pago_updated_at')->nullable()->after('estado_pago');

            // URL del comprobante de pago (Cloudinary)
            $table->string('comprobante_url')->nullable()->after('estado_pago_updated_at');

            // Fecha de última actualización del estado de entrega
            $table->timestamp('estado_updated_at')->nullable()->after('comprobante_url');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['estado_pago', 'estado_pago_updated_at', 'comprobante_url', 'estado_updated_at']);
        });
    }
};
