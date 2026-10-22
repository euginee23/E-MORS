<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Receipt numbers are numbered per market (each market starts at RCP-YYYY-0001),
 * so they only need to be unique within a market. A global unique index made the
 * first collection in every market after the first fail with a duplicate key.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropUnique(['receipt_number']);
            $table->unique(['market_id', 'receipt_number']);
        });
    }

    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropUnique(['market_id', 'receipt_number']);
            $table->unique('receipt_number');
        });
    }
};
