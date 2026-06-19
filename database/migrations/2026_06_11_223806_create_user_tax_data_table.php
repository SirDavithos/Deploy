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
        Schema::create('user_tax_data', function (Blueprint $table) {
            $table->id();
            // Relación 1 a N con el usuario
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Datos específicos para la factura/recibo en Bolivia
            $table->string('nit_or_ci'); // Puede ser un NIT o un CI para la factura
            $table->string('business_name'); // Razón Social (Ej: "Juan Pérez", "Banco Unión S.A.")
            
            // Opcional: El régimen tributario solo nos importará si este perfil lo usa un Artesano para vender
            $table->string('tax_regimen')->nullable(); // Simplificado, General, etc.
            
            $table->string('alias')->nullable(); // Ej: "Mi Trabajo", "Personal", "Factura de mi Mamá"
            $table->boolean('is_default')->default(false); // Para sugerir una por defecto
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_tax_data');
    }
};
