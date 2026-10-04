<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stall_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stall_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->date('start_date')->nullable();
            // Null while the vendor still holds the stall.
            $table->date('end_date')->nullable();
            $table->date('rent_expiry')->nullable();
            $table->decimal('monthly_rate', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['stall_id', 'end_date']);
        });

        // Only the current holder of each stall is known — earlier renters were
        // overwritten before this table existed.
        $now = now();

        DB::table('stalls')
            ->whereNotNull('vendor_id')
            ->orderBy('id')
            ->each(function ($stall) use ($now) {
                DB::table('stall_assignments')->insert([
                    'market_id' => $stall->market_id,
                    'stall_id' => $stall->id,
                    'vendor_id' => $stall->vendor_id,
                    'start_date' => $stall->rent_start ?? ($stall->created_at ? substr($stall->created_at, 0, 10) : $now->toDateString()),
                    'end_date' => null,
                    'rent_expiry' => $stall->rent_expiry,
                    'monthly_rate' => $stall->monthly_rate,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('stall_assignments');
    }
};
