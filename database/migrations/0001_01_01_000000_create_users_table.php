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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Identidad Personal Estática
            $table->string('first_name');
            $table->string('paternal_last_name');
            $table->string('maternal_last_name')->nullable();
            $table->string('ci_number')->unique();
            $table->string('ci_extension', 2); 
            $table->date('birth_date');
            
            // Credenciales y Cuenta
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('status')->default('active'); 
            $table->string('avatar')->nullable();
            
            // Seguridad y Legal
            $table->timestamp('accepted_terms_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            
            $table->rememberToken();
            $table->softDeletes(); 
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
