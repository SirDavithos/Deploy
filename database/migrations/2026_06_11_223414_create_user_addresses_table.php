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
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('city')->default('La Paz'); // La Paz, El Alto, etc.
            $table->string('zone'); // Sopocachi, Centro, etc.
            $table->string('street_address'); // Calle/Avenida/Pasaje y número
            $table->text('reference')->nullable(); // "Frente a...", "A media cuadra de..."
            
            // Coordenadas GPS de alta precisión
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            $table->boolean('is_default')->default(false); // Para saber cuál es su dirección principal
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
