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
        Schema::create('agent_credit_transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('investor_id')->constrained('users')->restrictOnDelete();
            $table->decimal('amount_usd', 18, 4);
            $table->string('idempotency_key', 64)->nullable();
            $table->timestamps();

            $table->unique(['agent_id', 'idempotency_key'], 'agent_credit_transfers_agent_id_idempotency_key_unique');
            $table->index('agent_id');
            $table->index('investor_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_credit_transfers');
    }
};
