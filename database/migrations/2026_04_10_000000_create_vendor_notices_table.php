<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('collection_id')->nullable()->constrained()->nullOnDelete();
            $table->string('notice_type');
            $table->string('issue_key')->unique();
            $table->date('issue_date')->nullable();
            $table->json('details')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->unsignedInteger('sent_count')->default(0);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['market_id', 'vendor_id']);
            $table->index(['notice_type', 'resolved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_notices');
    }
};
