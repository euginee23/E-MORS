<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stalls', function (Blueprint $table) {
            $table->date('rent_start')->nullable()->after('status');
            $table->date('rent_expiry')->nullable()->after('rent_start');
        });
    }

    public function down(): void
    {
        Schema::table('stalls', function (Blueprint $table) {
            $table->dropColumn(['rent_start', 'rent_expiry']);
        });
    }
};
