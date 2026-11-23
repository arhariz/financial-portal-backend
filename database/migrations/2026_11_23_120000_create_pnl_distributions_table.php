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
        Schema::create('pnl_distributions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('symbol_id')->constrained('symbols')->restrictOnDelete();
            $table->date('trade_date');
            $table->decimal('profit_loss', 18, 4);
            $table->foreignId('entry_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('draft');
            $table->timestamp('distributed_at')->nullable();
            $table->foreignId('reversal_of_id')->nullable()->constrained('pnl_distributions')->restrictOnDelete();
            $table->timestamps();

            $table->index('symbol_id');
            $table->index('trade_date');
            $table->index('status');
            $table->index('entry_by');
            $table->index('reversal_of_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pnl_distributions');
    }
};
