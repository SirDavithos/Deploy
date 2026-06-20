<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('shop_tax_data')
            ->whereNotIn('tax_regimen', ['Régimen General', 'Régimen Tributario Simplificado'])
            ->update(['tax_regimen' => null]);

        DB::statement("ALTER TABLE shop_tax_data ADD CONSTRAINT check_tax_regimen CHECK (tax_regimen IN ('Régimen General', 'Régimen Tributario Simplificado'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE shop_tax_data DROP CONSTRAINT IF EXISTS check_tax_regimen");
    }
};