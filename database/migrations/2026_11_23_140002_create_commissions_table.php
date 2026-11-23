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
        Schema::create('commissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->restrictOnDelete();
            $table->foreignId('rule_id')->constrained('commission_rules')->restrictOnDelete();
            $table->string('event', 40);
            $table->foreignId('source_user_id')->constrained('users')->restrictOnDelete();
            $table->string('source_reference_type');
            $table->unsignedBigInteger('source_reference_id');
            $table->decimal('base_amount', 18, 4);
            $table->decimal('amount', 18, 4);
            $table->string('status', 20)->default('pending');
            $table->timestamp('vest_at')->nullable();
            $table->foreignId('wallet_transaction_id')->nullable()->constrained('wallet_transactions')->nullOnDelete();
            $table->timestamps();

            $table->index('agent_id');
            $table->index('rule_id');
            $table->index('event');
            $table->index('source_user_id');
            $table->index(['source_reference_type', 'source_reference_id']);
            $table->index('status');
            $table->index('vest_at');
            $table->index('wallet_transaction_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
