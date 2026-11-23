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
        Schema::create('manual_deposits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->decimal('amount_usd', 18, 4);
            $table->decimal('amount_bdt', 18, 4);
            $table->foreignId('exchange_rate_id')->constrained('exchange_rates')->restrictOnDelete();
            $table->string('payment_method', 50);
            $table->string('proof_path');
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('wallet_transaction_id')->nullable()->constrained('wallet_transactions')->restrictOnDelete();
            $table->timestamps();

            $table->index('user_id');
            $table->index('exchange_rate_id');
            $table->index('status');
            $table->index('reviewed_by');
            $table->index('wallet_transaction_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_deposits');
    }
};
