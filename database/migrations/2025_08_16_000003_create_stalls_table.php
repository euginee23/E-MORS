<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stalls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stall_number');
            $table->string('section');
            $table->string('size')->default('3x3m');
            $table->decimal('monthly_rate', 10, 2)->default(0);
            $table->string('status')->default('available');
            $table->timestamps();

            $table->unique(['market_id', 'stall_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stalls');
    }
};
