<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('internal_transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('from_wallet_type', 20);
            $table->string('to_wallet_type', 20);
            $table->decimal('amount_usd', 18, 4);
            $table->foreignId('symbol_id')->nullable()->constrained('symbols')->restrictOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->restrictOnDelete();
            $table->string('idempotency_key', 64)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->unique(['user_id', 'idempotency_key'], 'internal_transfers_user_id_idempotency_key_unique');
            $table->index('user_id');
            $table->index('symbol_id');
            $table->index('position_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_transfers');
    }
};
