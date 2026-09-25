<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The GCash / bank transaction reference, so a non-cash payment can be traced
     * back to the vendor's transfer. Cash payments leave it null.
     */
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->string('reference_number', 100)->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropColumn('reference_number');
        });
    }
};
