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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // comprador
            $table->foreignId('shop_id')->constrained()->onDelete('cascade'); // tienda a la que pertenece el pedido (puede ser multi-tienda si lo prefieres simple)
            $table->foreignId('address_id')->nullable()->constrained('user_addresses')->nullOnDelete();
            $table->foreignId('tax_data_id')->nullable()->constrained('user_tax_data')->nullOnDelete();
            $table->string('status')->default('pending'); // pending, confirmed, shipped, delivered, cancelled
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
