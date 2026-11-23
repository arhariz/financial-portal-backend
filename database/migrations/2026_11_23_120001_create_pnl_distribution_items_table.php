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
        Schema::create('pnl_distribution_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('distribution_id')->constrained('pnl_distributions')->restrictOnDelete();
            $table->foreignId('position_id')->constrained('positions')->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->decimal('share_ratio', 10, 6);
            $table->decimal('amount', 18, 4);
            $table->foreignId('wallet_transaction_id')->nullable()->constrained('wallet_transactions')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('distribution_id');
            $table->index('position_id');
            $table->index('user_id');
            $table->index('wallet_transaction_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pnl_distribution_items');
    }
};
