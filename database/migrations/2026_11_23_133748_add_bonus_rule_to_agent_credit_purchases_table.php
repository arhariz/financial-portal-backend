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
        Schema::table('agent_credit_purchases', function (Blueprint $table): void {
            $table->foreignId('bonus_rule_id')
                ->nullable()
                ->after('bonus_amount_usd')
                ->constrained('agent_credit_bonus_rules')
                ->nullOnDelete();
            $table->unsignedTinyInteger('agent_tier')->nullable()->after('bonus_rule_id');

            $table->index('bonus_rule_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_credit_purchases', function (Blueprint $table): void {
            $table->dropIndex(['bonus_rule_id']);
            $table->dropColumn(['bonus_rule_id', 'agent_tier']);
        });
    }
};
