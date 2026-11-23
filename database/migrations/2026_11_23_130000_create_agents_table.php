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
        Schema::create('agents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $table->string('referral_code')->unique();
            $table->unsignedTinyInteger('tier');
            $table->foreignId('upline_agent_id')->nullable()->constrained('agents')->restrictOnDelete();
            $table->string('status', 20)->default('active');
            $table->decimal('total_sales', 18, 4)->default(0);
            $table->unsignedInteger('active_referrals_count')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index('tier');
            $table->index('upline_agent_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
