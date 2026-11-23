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
        Schema::create('referrals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->restrictOnDelete();
            $table->foreignId('referred_user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('level');
            $table->timestamp('created_at')->useCurrent();

            $table->index('agent_id');
            $table->index('referred_user_id');
            $table->index('level');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
