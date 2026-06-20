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
        Schema::create('shop_tax_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->string('nit_or_ci');
            $table->string('business_name');
            $table->string('tax_regimen')->nullable();
            $table->string('alias')->nullable();
            $table->boolean('is_default')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_tax_data');
    }
};
