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
        Schema::create('positions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('symbol_id')->constrained('symbols')->restrictOnDelete();
            $table->decimal('capital', 18, 4);
            $table->decimal('original_amount', 18, 4);
            $table->date('investment_date');
            $table->date('eligible_date');
            $table->string('status', 20)->default('active');
            $table->string('source', 20);
            $table->timestamps();

            $table->index('user_id');
            $table->index('symbol_id');
            $table->index('investment_date');
            $table->index('eligible_date');
            $table->index('status');
            $table->index('source');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
