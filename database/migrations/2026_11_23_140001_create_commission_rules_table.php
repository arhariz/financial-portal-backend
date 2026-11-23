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
        Schema::create('commission_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('event', 40);
            $table->unsignedTinyInteger('agent_tier');
            $table->unsignedInteger('level');
            $table->decimal('rate', 10, 6);
            $table->boolean('enabled');
            $table->unsignedInteger('holding_period_hours');
            $table->foreignId('deduction_profile_id')->nullable()->constrained('deduction_profiles')->nullOnDelete();
            $table->timestamps();

            $table->index('event');
            $table->index('agent_tier');
            $table->index('level');
            $table->index('enabled');
            $table->index('deduction_profile_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_rules');
    }
};
