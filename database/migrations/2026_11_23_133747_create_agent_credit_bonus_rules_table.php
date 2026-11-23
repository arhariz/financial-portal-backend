<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Admin-managed role bonus rates, one per agent tier (D-096).
     * No rate is seeded — GOV-01 forbids inventing a business value.
     */
    public function up(): void
    {
        Schema::create('agent_credit_bonus_rules', function (Blueprint $table): void {
            $table->id();
            $table->unsignedTinyInteger('tier')->unique();
            $table->decimal('rate', 10, 6);
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_credit_bonus_rules');
    }
};
