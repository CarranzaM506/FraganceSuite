<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Agregar índices para optimizar búsquedas del carrito
     */
    public function up(): void
    {
        // Índice para búsquedas rápidas de carritos por usuario
        Schema::table('cart', function (Blueprint $table) {
            $table->index('iduser', 'idx_cart_iduser');
        });

        // Índices para búsquedas rápidas en cartdetail
        Schema::table('cartdetail', function (Blueprint $table) {
            $table->index('idcart', 'idx_cartdetail_idcart');
            $table->index('idproduct', 'idx_cartdetail_idproduct');
        });

        // Índice compuesto único para evitar duplicados y acelerar búsquedas
        // (ya debería existir como clave primaria compuesta)
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('cart', function (Blueprint $table) {
            $table->dropIndex('idx_cart_iduser');
        });

        Schema::table('cartdetail', function (Blueprint $table) {
            $table->dropIndex('idx_cartdetail_idcart');
            $table->dropIndex('idx_cartdetail_idproduct');
        });
    }
};
