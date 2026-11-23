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
        Schema::create('agent_credit_purchases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->decimal('amount_bdt', 18, 4);
            $table->decimal('amount_usd', 18, 4);
            $table->decimal('bonus_amount_usd', 18, 4);
            $table->foreignId('exchange_rate_id')->constrained('exchange_rates')->restrictOnDelete();
            $table->string('idempotency_key', 64)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'idempotency_key'], 'agent_credit_purchases_user_id_idempotency_key_unique');
            $table->index('user_id');
            $table->index('exchange_rate_id');
            $table->index('created_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_credit_purchases');
    }
};
